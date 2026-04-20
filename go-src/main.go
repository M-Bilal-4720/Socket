package main

import (
	"encoding/json"
	"fmt"
	"net"
	"net/http"
	"os"
	"os/signal"
	"strings"
	"sync"
	"syscall"

	"github.com/gorilla/websocket"
)

type Message struct {
	Channel string      `json:"channel"`
	Event   string      `json:"event"`
	Data    interface{} `json:"data"`
}

type ClientInfo struct {
	Channels  map[string]bool
	JWT       string
	Connected bool
}

var clients = make(map[*websocket.Conn]*ClientInfo)
var mu sync.Mutex
var upgrader = websocket.Upgrader{CheckOrigin: func(r *http.Request) bool { return true }}
var connectionMode = "public" // "public" or "private"
var jwtSecret = ""

func main() {
	socketPath := "/tmp/larago.sock"
	port := os.Getenv("LARAGO_PORT")
	if port == "" {
		port = "8080"
	}
	host := os.Getenv("LARAGO_HOST")
	if host == "" {
		host = "0.0.0.0"
	}
	connectionMode = os.Getenv("LARAGO_CONNECTION_MODE")
	if connectionMode == "" {
		connectionMode = "public"
	}
	jwtSecret = os.Getenv("LARAGO_JWT_SECRET")

	// Clean up stale socket file
	cleanupSocket(socketPath)

	// Handle graceful shutdown
	sigChan := make(chan os.Signal, 1)
	signal.Notify(sigChan, os.Interrupt, syscall.SIGTERM)

	// Unix Socket for Laravel Communication
	go func() {
		l, err := net.Listen("unix", socketPath)
		if err != nil {
			panic(err)
		}
		fmt.Println("Unix Socket Listening on", socketPath)
		for {
			conn, err := l.Accept()
			if err != nil {
				fmt.Println("Error accepting connection:", err)
				continue
			}
			go handleLaravelMessage(conn)
		}
	}()

	// WebSocket Server
	http.HandleFunc("/ws", handleWebsocket)
	addr := host + ":" + port
	fmt.Println("LaraGo Engine running on", addr)
	fmt.Println("Connection Mode:", connectionMode)

	// Run server in goroutine
	go http.ListenAndServe(addr, nil)

	// Wait for shutdown signal
	<-sigChan
	fmt.Println("\nShutting down LaraGo Engine...")
	cleanupSocket(socketPath)
	os.Exit(0)
}

func cleanupSocket(socketPath string) {
	// Remove stale socket file if it exists
	if err := os.Remove(socketPath); err != nil && !os.IsNotExist(err) {
		// Silent cleanup - don't panic on permission errors
	}
}

func handleWebsocket(w http.ResponseWriter, r *http.Request) {
	ws, err := upgrader.Upgrade(w, r, nil)
	if err != nil {
		fmt.Println("WebSocket upgrade error:", err)
		return
	}

	// Check authentication if private mode
	if connectionMode == "private" {
		token := r.URL.Query().Get("token")
		if token == "" {
			ws.WriteJSON(map[string]string{
				"error": "Missing authentication token. Connection mode is private.",
			})
			ws.Close()
			return
		}

		// Basic JWT validation (check if token is not empty and has expected format)
		if !isValidJWT(token) {
			ws.WriteJSON(map[string]string{
				"error": "Invalid authentication token",
			})
			ws.Close()
			return
		}

		// Store authenticated client
		mu.Lock()
		clients[ws] = &ClientInfo{
			Channels:  make(map[string]bool),
			JWT:       token,
			Connected: true,
		}
		mu.Unlock()

		fmt.Println("Private connection authenticated")
	} else {
		// Public mode - no authentication needed
		mu.Lock()
		clients[ws] = &ClientInfo{
			Channels:  make(map[string]bool),
			Connected: true,
		}
		mu.Unlock()

		fmt.Println("Public connection established")
	}

	defer ws.Close()
	for {
		var msg map[string]interface{}
		if err := ws.ReadJSON(&msg); err != nil {
			break
		}

		if event, ok := msg["event"].(string); ok && event == "subscribe" {
			if channel, ok := msg["channel"].(string); ok {
				mu.Lock()
				if clientInfo, exists := clients[ws]; exists {
					clientInfo.Channels[channel] = true
				}
				mu.Unlock()
				fmt.Println("Subscribed to:", channel)
			}
		}
	}

	// Remove client on disconnect
	mu.Lock()
	delete(clients, ws)
	mu.Unlock()
}

func isValidJWT(token string) bool {
	// Basic JWT validation: should have 3 parts separated by dots
	parts := strings.Split(token, ".")
	return len(parts) == 3 && parts[0] != "" && parts[1] != "" && parts[2] != ""
}

func handleLaravelMessage(c net.Conn) {
	defer c.Close()
	var m Message
	json.NewDecoder(c).Decode(&m)
	mu.Lock()
	defer mu.Unlock()

	for client, clientInfo := range clients {
		if clientInfo.Channels[m.Channel] {
			client.WriteJSON(m)
		}
	}
}
