# LaraGo Socket Package

Real-time WebSocket Broadcasting Engine for Laravel with Go Backend

⚡ **Zero configuration** - Auto-builds Go engine on first run via Artisan command  
✅ **Supports Laravel 8 - 13** - Including brand new Laravel 12 & 13  
🌍 **Cross-platform** - Works on Windows, macOS, and Linux with single codebase  

## Requirements

- **PHP:** 7.4 or higher
- **Laravel:** 8.0, 9.0, 10.0, 11.0, 12.0, or 13.0+
- **Go:** 1.20+ (auto-downloaded and used, no installation needed for binary build)
- **Composer:** For package management

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
# Start the WebSocket engine (default)
php artisan larago:run --background

# Custom port
php artisan larago:run --port=9000 --background

# Custom host and port
php artisan larago:run --host=127.0.0.1 --port=3000 --background

# Generate JWT token for private channels
php artisan larago:token --user-id=1 --expires=3600

# Stop the engine
php artisan larago:stop
```

**The engine will automatically build the Go binary on first run if needed!**

## Features

✨ **Zero Configuration** - Auto-builds Go binary on first run  
🔐 **Channel-Level Access Control:**
   - Public Channels - Anyone can subscribe
   - Private Channels - Require JWT authentication (prefix: `private-`)

🚀 **Highly Configurable:**
   - Custom WebSocket port (`--port=8080`)
   - Custom host (`--host=0.0.0.0`)
   - Custom Laravel communication port (auto: 6001)

🔄 **Built-in Testing:**
   - Browser-based test page at `/larago-test`
   - Test both public and private channels
   - Real-time message monitoring

⚡ **Background Process Management:**
   - Runs as background process with `--background`
   - Auto-detach process
   - Process monitoring and logging

🌍 **Cross-Platform:**
   - Windows 10/11+ with Go installed
   - macOS 10.15+ with Go installed
   - Linux (Ubuntu, Debian, CentOS, etc.)

📚 **Comprehensive Documentation:**
   - Setup guides for each OS (WINDOWS_SETUP.md, MACOS_SETUP.md, LINUX_SETUP.md)
   - Quick fix guide for Windows (WINDOWS_QUICK_FIX.md)
   - Channel architecture guide (CONNECTION_MODES.md)

See [CONNECTION_MODES.md](CONNECTION_MODES.md) for detailed channel architecture and security guide.

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
   - WebSocket on port 8080 (customizable) for frontend connections
   - TCP socket on 127.0.0.1:6001 for Laravel backend communication

2. **Laravel** sends broadcast messages to Go via TCP socket

3. **Go** routes messages to WebSocket clients subscribed to the channel

## Architecture

```
Laravel App → TCP Socket → Go Engine → WebSocket → Frontend
            (127.0.0.1:6001)   (:8080)

┌─────────────────────────────────────────────────────────┐
│ Single WebSocket Connection Supports:                   │
│ • Public Channels (anyone can subscribe)                │
│ • Private Channels (require JWT authentication)         │
└─────────────────────────────────────────────────────────┘
```

**Benefits:**
- ✅ Cross-platform: TCP works on Windows, Mac, Linux
- ✅ No dependency on Unix-specific features
- ✅ Easier debugging and monitoring
- ✅ Better Windows support
