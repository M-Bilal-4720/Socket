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
	Connected bool
}

var clients = make(map[*websocket.Conn]*ClientInfo)
var mu sync.Mutex
var upgrader = websocket.Upgrader{CheckOrigin: func(r *http.Request) bool { return true }}
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
	fmt.Println("Supports public and private channels")

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

	// Store client - all clients can connect
	mu.Lock()
	clients[ws] = &ClientInfo{
		Channels:  make(map[string]bool),
		Connected: true,
	}
	mu.Unlock()

	fmt.Println("Client connected")

	defer ws.Close()
	for {
		var msg map[string]interface{}
		if err := ws.ReadJSON(&msg); err != nil {
			break
		}

		if event, ok := msg["event"].(string); ok && event == "subscribe" {
			if channel, ok := msg["channel"].(string); ok {
				// Check if this is a private channel
				if strings.HasPrefix(channel, "private-") {
					// Private channel - require JWT token
					token, ok := msg["token"].(string)
					if !ok || token == "" {
						ws.WriteJSON(map[string]string{
							"error":   "private_channel_requires_token",
							"message": "Private channels require a JWT token",
							"channel": channel,
						})
						fmt.Println("Rejected: Missing token for private channel:", channel)
						continue
					}

					// Validate JWT
					if !isValidJWT(token) {
						ws.WriteJSON(map[string]string{
							"error":   "invalid_token",
							"message": "Invalid JWT token",
							"channel": channel,
						})
						fmt.Println("Rejected: Invalid token for private channel:", channel)
						continue
					}

					// Valid token - subscribe to private channel
					mu.Lock()
					if clientInfo, exists := clients[ws]; exists {
						clientInfo.Channels[channel] = true
					}
					mu.Unlock()
					ws.WriteJSON(map[string]string{
						"status":  "subscribed",
						"channel": channel,
						"type":    "private",
					})
					fmt.Println("Subscribed to private channel:", channel)
				} else {
					// Public channel - no authentication needed
					mu.Lock()
					if clientInfo, exists := clients[ws]; exists {
						clientInfo.Channels[channel] = true
					}
					mu.Unlock()
					ws.WriteJSON(map[string]string{
						"status":  "subscribed",
						"channel": channel,
						"type":    "public",
					})
					fmt.Println("Subscribed to public channel:", channel)
				}
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
