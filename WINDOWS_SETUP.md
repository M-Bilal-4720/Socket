# LaraGo - Windows Installation Guide

## 🎯 System Requirements

- **Windows 10/11** or Server 2019+
- **Go 1.20+** installed
- **PHP 8.0+** with Laravel 10+
- **Composer** installed

## 📦 Step 1: Install Go on Windows

1. Download from: https://golang.org/dl/
2. Choose **go1.20.X.windows-amd64.msi** (or later)
3. Run the installer and follow the steps
4. Verify installation:
   ```bash
   go version
   ```

## 🚀 Step 2: Install LaraGo Package

```bash
composer require larago/socket
```

The package will be installed to `vendor/larago/socket/`

## 🔨 Step 3: Build Go Engine for Windows

### Option A: Using Batch Script (Recommended)

```bash
cd vendor/larago/socket
build.bat
```

This creates `bin/go-engine.exe`

### Option B: Manual Build

```bash
cd vendor/larago/socket
go build -o bin/go-engine.exe go-src/main.go
```

## 🌐 Step 4: Start the Engine

```bash
php artisan larago:run --port=8080
```

Or run in background:

```bash
php artisan larago:run --background --port=8080
```

**Windows Output:**
```
🚀 Starting LaraGo Engine...
📡 WebSocket on 0.0.0.0:8080
🔗 Laravel Communication on 127.0.0.1:6001
🔐 Supports both public and private channels
🔄 Running in background mode

✅ Engine started successfully in background
Log: storage\logs\larago-engine.log
Stop with: php artisan larago:stop or taskkill /IM go-engine.exe /F
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
taskkill /IM go-engine.exe /F
```

Or in PowerShell:
```powershell
Stop-Process -Name "go-engine" -Force
```

## 🐛 Troubleshooting on Windows

### Issue: "Port already in use"
```bash
netstat -ano | findstr :8080
taskkill /PID <PID> /F
```

### Issue: "Engine failed to start"
Check logs:
```bash
type storage\logs\larago-engine.log
```

### Issue: Go build fails
Ensure Go is in PATH:
```bash
go version
```

If not found, restart PowerShell/CMD after installing Go.

### Issue: Permission denied
Run PowerShell as Administrator:
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned
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

## 📚 Full Documentation

See `README.md` for complete usage guide.

## 💬 Support

For issues, check:
1. Engine logs: `storage/logs/larago-engine.log`
2. Port conflicts: `netstat -ano | findstr :8080`
3. Go installation: `go version`
