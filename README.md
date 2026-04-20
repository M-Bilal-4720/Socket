# LaraGo Socket Package

Real-time WebSocket Broadcasting Engine for Laravel with Go Backend

⚡ **Zero configuration** - Auto-builds Go engine on first run via Artisan command

## Installation

### Option 1: From Packagist (Recommended)

```bash
composer require larago/socket
```

Then start the engine:
```bash
php artisan larago:run
```

> **Note:** The Go engine binary is automatically built on first run. No manual compilation needed!

### Option 2: From GitHub (Development)

```bash
# Add VCS repository configuration
composer config repositories.socket vcs https://github.com/M-Bilal-4720/Socket.git

# Install the package
composer require larago/socket:dev-master --prefer-source
```

Then start the engine:
```bash
php artisan larago:run
```

### Option 3: From Local Path (Development)

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
```

Start the engine:
```bash
php artisan larago:run
```

## Configuration

### Step 1: Configure Broadcasting Driver

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

### Step 2: Run Go Engine (Artisan Command)

```bash
# Start the WebSocket engine with auto-build
php artisan larago:run

# Custom port
php artisan larago:run --port=9000

# Custom host
php artisan larago:run --host=127.0.0.1
```

**The engine will automatically build the Go binary on first run if needed!**

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
