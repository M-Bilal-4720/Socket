# LaraGo Socket Package

[![Latest Stable Version](https://poser.pugx.org/larago/socket/v)](https://packagist.org/packages/larago/socket)
[![Total Downloads](https://poser.pugx.org/larago/socket/downloads)](https://packagist.org/packages/larago/socket)
[![License](https://poser.pugx.org/larago/socket/license)](https://github.com/M-Bilal-4720/Socket/blob/master/LICENSE)
[![PHP Version Require](https://poser.pugx.org/larago/socket/require/php)](https://packagist.org/packages/larago/socket)
[![Laravel Support](https://img.shields.io/badge/Laravel-8%20%7C%209%20%7C%2010%20%7C%2011%20%7C%2012%20%7C%2013-brightgreen)](https://packagist.org/packages/larago/socket)

Real-time WebSocket Broadcasting Engine for Laravel with Go Backend

⚡ **Zero configuration** - Auto-builds Go engine on first run via Artisan command  
✅ **Supports Laravel 8 - 13** - Including brand new Laravel 12 & 13  
🌍 **Cross-platform** - Works on Windows, macOS, and Linux with single codebase  

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Features](#features)
- [How It Works](#how-it-works)
- [Platform-Specific Guides](#platform-specific-guides)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)
- [License](#license)

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

## Platform-Specific Guides

### Windows
See [WINDOWS_SETUP.md](WINDOWS_SETUP.md) for detailed Windows installation and troubleshooting.
Quick fix: [WINDOWS_QUICK_FIX.md](WINDOWS_QUICK_FIX.md)

**Key Steps:**
```bash
# Build the engine
build.bat

# Run the engine
php artisan larago:run --background
```

### macOS
See [MACOS_SETUP.md](MACOS_SETUP.md) for detailed macOS setup.

**Key Steps:**
```bash
# Build the engine
bash build.sh

# Run the engine
php artisan larago:run --background
```

### Linux
See [LINUX_SETUP.md](LINUX_SETUP.md) for detailed Linux setup with Systemd configuration.

**Key Steps:**
```bash
# Build the engine
bash build.sh

# Run as service
sudo systemctl start larago.service
```

## Testing

### Browser Test Page

Once the engine is running, open:
```
http://localhost:8000/larago-test
```

### Test Public Channels
```javascript
const ws = new WebSocket('ws://localhost:8080/ws');
ws.send(JSON.stringify({
    event: 'subscribe',
    channel: 'public-chat'
}));
```

### Test Private Channels
```javascript
const ws = new WebSocket('ws://localhost:8080/ws');
ws.send(JSON.stringify({
    event: 'subscribe',
    channel: 'private-user-1',
    token: 'your-jwt-token-here'
}));
```

Generate token:
```bash
php artisan larago:token --user-id=1
```

## Troubleshooting

### Port Already In Use
```bash
# Find process on port 8080
lsof -i :8080  # macOS/Linux
netstat -ano | findstr :8080  # Windows

# Kill process or use different port
php artisan larago:run --port=9000
```

### Go Not Installed
1. Download from https://golang.org/dl/
2. Install and verify: `go version`
3. Restart terminal and try again

### Windows Build Issues
- Run Command Prompt as Administrator
- Ensure Go is in PATH: `go version`
- Check build.bat output for specific errors

### Engine Won't Start
Check logs:
```bash
tail -f storage/logs/larago-engine.log  # macOS/Linux
type storage\logs\larago-engine.log     # Windows
```

### Connection Issues
- Verify WebSocket URL: `ws://localhost:8080/ws`
- Check if port 8080 is accessible: `curl -i http://localhost:8080/ws`
- Check Laravel communication: `php artisan larago:test-broadcast`

## Channel Architecture

For detailed information about public and private channels, see [CONNECTION_MODES.md](CONNECTION_MODES.md)

### Public Channels
- Anyone can subscribe
- No authentication required
- Pattern: `any-channel-name`

### Private Channels
- Require JWT token to subscribe
- Automatically identified by `private-` prefix
- Pattern: `private-user-1`, `private-room-5`, etc.

## Commands Reference

```bash
# Start engine in foreground
php artisan larago:run

# Start engine in background
php artisan larago:run --background

# Use custom port
php artisan larago:run --port=9000

# Use custom host and port
php artisan larago:run --host=127.0.0.1 --port=3000

# Force kill existing engine before starting
php artisan larago:run --force

# Stop the engine gracefully
php artisan larago:stop

# Force stop the engine
php artisan larago:stop --force

# Generate JWT token
php artisan larago:token --user-id=1 --expires=3600

# Test broadcasts
php artisan larago:test-broadcast
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The LaraGo Socket package is open-sourced software licensed under the [MIT license](LICENSE).
