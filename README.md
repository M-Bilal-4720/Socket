# LaraGo Socket Package

Integrated Laravel Broadcasting with Go Real-time WebSocket Engine

## Installation

### Option 1: From GitHub (Recommended)

```bash
# Add VCS repository configuration
composer config repositories.socket vcs https://github.com/M-Bilal-4720/Socket.git

# Install the package
composer require larago/socket:dev-master --prefer-source

# Compile Go engine
cd vendor/larago/socket && bash build.sh && cd ../../../
```

### Option 2: From Local Path (Development)

Add to your main `composer.json`:

```json
"repositories": [
    { "type": "path", "url": "./packages/LaraGo" }
],
"require": {
    "larago/socket": "*"
}
```

Then run:
```bash
composer update

# Manually compile Go engine
cd vendor/larago/socket && bash build.sh
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

### Step 3: Run Go Engine

```bash
# Start the WebSocket engine (runs on port 8080)
vendor/larago/socket/bin/go-engine
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
