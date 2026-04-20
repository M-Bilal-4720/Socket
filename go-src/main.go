package main

import (
	"encoding/json"
	"fmt"
	"net"
	"net/http"
	"sync"

	"github.com/gorilla/websocket"
)

type Message struct {
	Channel string      `json:"channel"`
	Event   string      `json:"event"`
	Data    interface{} `json:"data"`
}

var clients = make(map[*websocket.Conn]map[string]bool)
var mu sync.Mutex
var upgrader = websocket.Upgrader{CheckOrigin: func(r *http.Request) bool { return true }}

func main() {
	// Unix Socket for Laravel Communication
	go func() {
		socketPath := "/tmp/larago.sock"
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
	fmt.Println("LaraGo Engine running on :8080")
	http.ListenAndServe(":8080", nil)
}

func handleWebsocket(w http.ResponseWriter, r *http.Request) {
	ws, err := upgrader.Upgrade(w, r, nil)
	if err != nil {
		fmt.Println("WebSocket upgrade error:", err)
		return
	}
	mu.Lock()
	clients[ws] = make(map[string]bool)
	mu.Unlock()

	defer ws.Close()
	for {
		var msg map[string]string
		if err := ws.ReadJSON(&msg); err != nil {
			break
		}
		if msg["event"] == "subscribe" {
			mu.Lock()
			clients[ws][msg["channel"]] = true
			mu.Unlock()
			fmt.Println("Subscribed to:", msg["channel"])
		}
	}

	// Remove client on disconnect
	mu.Lock()
	delete(clients, ws)
	mu.Unlock()
}

func handleLaravelMessage(c net.Conn) {
	defer c.Close()
	var m Message
	json.NewDecoder(c).Decode(&m)
	mu.Lock()
	defer mu.Unlock()

	for client, subs := range clients {
		if subs[m.Channel] {
			client.WriteJSON(m)
		}
	}
}
