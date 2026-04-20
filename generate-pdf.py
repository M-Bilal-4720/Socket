import os
from weasyprint import HTML

# Define the package content
content = """
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4;
            margin: 20mm;
            background-color: #f4f7f6;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
            font-size: 11pt;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        h1 { margin: 0; font-size: 22pt; }
        h2 { color: #2980b9; border-bottom: 2px solid #2980b9; padding-bottom: 5px; margin-top: 30px; }
        h3 { color: #16a085; }
        pre {
            background-color: #282c34;
            color: #abb2bf;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-family: 'Courier New', Courier, monospace;
            font-size: 9pt;
        }
        .file-path {
            font-weight: bold;
            color: #e67e22;
            margin-top: 15px;
            display: block;
        }
        .note {
            background-color: #fff3cd;
            border-left: 5px solid #ffecb5;
            padding: 10px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LaraGo Socket Package</h1>
        <p>Integrated Laravel Broadcasting with Golang Real-time Engine</p>
    </div>

    <h2>1. Folder Structure</h2>
    <p>Create the following structure in your Laravel root:</p>
    <pre>
laravel-root/
├── packages/
│   └── LaraGo/
│       └── Socket/
│           ├── src/
│           │   ├── GoSocketServiceProvider.php
│           │   └── GoBroadcaster.php
│           ├── go-src/
│           │   └── main.go
│           └── composer.json
    </pre>

    <h2>2. Golang Engine (The Real-time Core)</h2>
    <span class="file-path">File: packages/LaraGo/Socket/go-src/main.go</span>
    <pre>
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
        _ = net.Listen("unix", socketPath) // In production, handle cleanup
        l, err := net.Listen("unix", socketPath)
        if err != nil { panic(err) }
        fmt.Println("Unix Socket Listening...")
        for {
            conn, _ := l.Accept()
            go handleLaravelMessage(conn)
        }
    }()

    // WebSocket Server
    http.HandleFunc("/ws", handleWebsocket)
    fmt.Println("LaraGo Engine running on :8080")
    http.ListenAndServe(":8080", nil)
}

func handleWebsocket(w http.ResponseWriter, r *http.Request) {
    ws, _ := upgrader.Upgrade(w, r, nil)
    mu.Lock()
    clients[ws] = make(map[string]bool)
    mu.Unlock()

    defer ws.Close()
    for {
        var msg map[string]string
        if err := ws.ReadJSON(&msg); err != nil { break }
        if msg["event"] == "subscribe" {
            mu.Lock()
            clients[ws][msg["channel"]] = true
            mu.Unlock()
            fmt.Println("Subscribed to:", msg["channel"])
        }
    }
}

func handleLaravelMessage(c net.Conn) {
    var m Message
    json.NewDecoder(c).Decode(&m)
    mu.Lock()
    for client, subs := range clients {
        if subs[m.Channel] {
            client.WriteJSON(m)
        }
    }
    mu.Unlock()
    c.Close()
}
    </pre>

    <h2>3. Laravel Broadcasting Driver</h2>
    <span class="file-path">File: packages/LaraGo/Socket/src/GoBroadcaster.php</span>
    <pre>
&lt;?php

namespace LaraGo\Socket;

use Illuminate\Contracts\Broadcasting\Broadcaster;

class GoBroadcaster implements Broadcaster
{
    public function broadcast(array $channels, $event, array $payload = [])
    {
        $socketPath = "/tmp/larago.sock";
        $fp = @stream_socket_client("unix://$socketPath", $errno, $errstr);

        if (!$fp) return;

        foreach ($channels as $channel) {
            fwrite($fp, json_encode([
                'channel' => (string) $channel,
                'event'   => $event,
                'data'    => $payload
            ]));
        }
        fclose($fp);
    }

    public function auth($request) { return true; }
    public function validAuthenticationResponse($request, $result) { return []; }
}
    </pre>

    <h2>4. Service Provider</h2>
    <span class="file-path">File: packages/LaraGo/Socket/src/GoSocketServiceProvider.php</span>
    <pre>
&lt;?php

namespace LaraGo\Socket;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class GoSocketServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Broadcast::extend('larago', function ($app) {
            return new GoBroadcaster();
        });
    }
}
    </pre>

    <h2>5. Installation Instructions</h2>
    <h3>Step A: Register Package</h3>
    <p>In your main <code>composer.json</code>:</p>
    <pre>
"repositories": [
    { "type": "path", "url": "packages/LaraGo/Socket" }
],
"require": {
    "larago/socket": "*"
}
    </pre>
    <p>Run <code>composer update</code></p>

    <h3>Step B: Config Broadcaster</h3>
    <p>In <code>config/broadcasting.php</code> add:</p>
    <pre>
'larago' => [
    'driver' => 'larago',
],
    </pre>
    <p>Set <code>BROADCAST_DRIVER=larago</code> in your <code>.env</code>.</p>

    <h3>Step C: Compile and Run Go</h3>
    <pre>
cd packages/LaraGo/Socket/go-src
go build -o ../bin/go-engine main.go
../bin/go-engine
    </pre>

    <div class="note">
        <strong>Usage:</strong> Now just use <code>broadcast(new YourEvent($data))</code> in Laravel. Your frontend should connect to <code>ws://localhost:8080/ws</code> and send a subscribe message for the channel.
    </div>
</body>
</html>
"""

# Generate PDF
output_path = "LaraGo-Socket-Package-Guide.pdf"
HTML(string=content).write_pdf(output_path)
print(f"PDF generated: {output_path}")
