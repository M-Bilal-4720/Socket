# LaraGo Socket Package

Integrated Laravel Broadcasting with Golang Real-time Engine

## Installation

### Step 1: Register Package in Laravel

Add to your main `composer.json`:

```json
"repositories": [
    { "type": "path", "url": "packages/LaraGo/Socket" }
],
"require": {
    "larago/socket": "*"
}
```

Then run:
```bash
composer update
```

### Step 2: Configure Broadcasting Driver

In `config/broadcasting.php`, add:

```php
'larago' => [
    'driver' => 'larago',
],
```

Set in `.env`:
```
BROADCAST_DRIVER=larago
```

### Step 3: Compile and Run Go Engine

```bash
cd packages/LaraGo/Socket/go-src
go get github.com/gorilla/websocket
go build -o ../bin/go-engine main.go
../bin/go-engine
```

## Usage

In your Laravel Event:

```php
broadcast(new YourEvent($data));
```

In your frontend, connect to WebSocket:

```javascript
const ws = new WebSocket('ws://localhost:8080/ws');
ws.onopen = () => {
    ws.send(JSON.stringify({
        event: 'subscribe',
        channel: 'your-channel'
    }));
};
ws.onmessage = (event) => {
    console.log(JSON.parse(event.data));
};
```

## How It Works

1. **Go Engine** listens on two interfaces:
   - WebSocket on port 8080 for frontend connections
   - Unix socket at `/tmp/larago.sock` for Laravel backend

2. **Laravel** sends broadcast messages to Go via Unix socket

3. **Go** routes messages to WebSocket clients subscribed to the channel

## Architecture

```
Laravel App → Unix Socket → Go Engine → WebSocket → Frontend
            (/tmp/larago.sock)         (:8080)
```
