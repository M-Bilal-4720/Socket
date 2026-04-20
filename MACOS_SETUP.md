# LaraGo - macOS Installation Guide

## 🎯 System Requirements

- **macOS 10.15+** (Catalina or newer)
- **Go 1.20+** installed
- **PHP 8.0+** with Laravel 10+
- **Composer** installed

## 📦 Step 1: Install Go on macOS

### Option A: Using Homebrew (Recommended)

```bash
brew install go
```

### Option B: Direct Download

1. Download from: https://golang.org/dl/
2. Choose **go1.20.X.darwin-amd64.tar.gz** (or later)
3. Extract and install:
   ```bash
   tar -C /usr/local -xzf go1.20.X.darwin-amd64.tar.gz
   ```

4. Add to PATH in `~/.zshrc` or `~/.bash_profile`:
   ```bash
   export PATH=$PATH:/usr/local/go/bin
   ```

5. Reload shell:
   ```bash
   source ~/.zshrc
   ```

6. Verify installation:
   ```bash
   go version
   ```

## 🚀 Step 2: Install LaraGo Package

```bash
composer require larago/socket
```

## 🔨 Step 3: Build Go Engine for macOS

```bash
cd vendor/larago/socket
bash build.sh
```

This creates `bin/go-engine`

## 🌐 Step 4: Start the Engine

### Foreground Mode (Development):
```bash
php artisan larago:run --port=8080
```

Press `Ctrl+C` to stop.

### Background Mode (Production):
```bash
php artisan larago:run --background --port=8080
```

**macOS Output:**
```
🚀 Starting LaraGo Engine...
📡 WebSocket on 0.0.0.0:8080
🔗 Laravel Communication on 127.0.0.1:6001
🔐 Supports both public and private channels
🔄 Running in background mode

✅ Engine started successfully in background
PID: 12345
Log: storage/logs/larago-engine.log
Stop with: php artisan larago:stop or pkill -f go-engine
```

## 🔐 Step 5: Generate JWT Token

```bash
php artisan larago:token --user-id=1
```

## 📝 Step 6: Test WebSocket Connection

Open your browser to: `http://localhost:8000/larago-test`

### Test Public Channel:
1. Enter channel name: `public-chat`
2. Click "Subscribe to Public Channel"
3. Should see ✅ subscribed

### Test Private Channel:
1. Enter channel name: `private-user-1`
2. Paste JWT token from Step 5
3. Click "Subscribe to Private Channel"
4. Should see ✅ subscribed and messages

## 🛑 Stop the Engine

**Graceful shutdown:**
```bash
php artisan larago:stop
```

**Force kill:**
```bash
pkill -9 go-engine
```

## 🐛 Troubleshooting on macOS

### Issue: "Port already in use"
```bash
lsof -i :8080
kill -9 <PID>
```

### Issue: "go: command not found"
Add Go to PATH:
```bash
export PATH=$PATH:/usr/local/go/bin
```

### Issue: "Permission denied" building
```bash
chmod +x vendor/larago/socket/build.sh
bash vendor/larago/socket/build.sh
```

### Issue: Engine won't start in background
Check logs:
```bash
tail -50 storage/logs/larago-engine.log
```

## 📋 Environment Variables

Optional configuration via `.env`:

```env
LARAGO_HOST=0.0.0.0          # Server listen address
LARAGO_PORT=8080             # WebSocket port
LARAGO_LARAVEL_PORT=6001     # Laravel communication port
```

## 🔗 Cross-Platform Architecture

**Windows, Mac, and Linux all use:**
- ✅ TCP sockets (not Unix sockets)
- ✅ WebSocket on TCP port (default 8080)
- ✅ Laravel communication on TCP 127.0.0.1:6001
- ✅ Same codebase for all platforms

## 💡 Launching on System Startup

Create a LaunchAgent for automatic startup:

**File:** `~/Library/LaunchAgents/com.larago.socket.plist`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>Label</key>
    <string>com.larago.socket</string>
    <key>ProgramArguments</key>
    <array>
        <string>/usr/bin/php</string>
        <string>/path/to/project/artisan</string>
        <string>larago:run</string>
        <string>--background</string>
    </array>
    <key>RunAtLoad</key>
    <true/>
    <key>StandardOutPath</key>
    <string>/path/to/project/storage/logs/larago.log</string>
    <key>StandardErrorPath</key>
    <string>/path/to/project/storage/logs/larago.log</string>
</dict>
</plist>
```

Load it:
```bash
launchctl load ~/Library/LaunchAgents/com.larago.socket.plist
```

## 📚 Full Documentation

See `README.md` for complete usage guide.

## 💬 Support

For issues, check:
1. Engine logs: `storage/logs/larago-engine.log`
2. Port conflicts: `lsof -i :8080`
3. Go installation: `go version`
