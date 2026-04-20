# LaraGo - Linux Installation Guide

## 🎯 System Requirements

- **Ubuntu 18.04+**, **Debian 9+**, **CentOS 7+**, or other Linux distributions
- **Go 1.20+** installed
- **PHP 8.0+** with Laravel 10+
- **Composer** installed
- **root** or **sudo** access for process management

## 📦 Step 1: Install Go on Linux

### Ubuntu/Debian:
```bash
wget https://golang.org/dl/go1.22.5.linux-amd64.tar.gz
sudo tar -C /usr/local -xzf go1.22.5.linux-amd64.tar.gz
```

### Add to PATH (~/.bashrc or ~/.zshrc):
```bash
export PATH=$PATH:/usr/local/go/bin
```

### Reload shell:
```bash
source ~/.bashrc
```

### Verify installation:
```bash
go version
```

## 🚀 Step 2: Install LaraGo Package

```bash
composer require larago/socket
```

## 🔨 Step 3: Build Go Engine for Linux

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

**Linux Output:**
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

## 🐛 Troubleshooting on Linux

### Issue: "Port already in use"
```bash
lsof -i :8080
kill -9 <PID>
```

Or use netstat:
```bash
netstat -tulpn | grep 8080
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

### Issue: "Address already in use" on startup
The socket port (6001) may be in use. Change it:
```bash
# In .env or via command
LARAGO_LARAVEL_PORT=6002
```

### Issue: Process won't stop gracefully
Force kill and verify:
```bash
pkill -f go-engine
sleep 1
pgrep -f go-engine  # Should return nothing
```

## 📋 Environment Variables

Optional configuration via `.env`:

```env
LARAGO_HOST=0.0.0.0          # Server listen address
LARAGO_PORT=8080             # WebSocket port
LARAGO_LARAVEL_PORT=6001     # Laravel communication port
LARAGO_JWT_SECRET=<base64-key> # Auto-set to Laravel APP_KEY
```

## 🚀 Running as Systemd Service

### Create service file:

**File:** `/etc/systemd/system/larago.service`

```ini
[Unit]
Description=LaraGo Socket Engine
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/home/youruser/yourproject
ExecStart=/usr/bin/php artisan larago:run
Restart=always
RestartSec=10
StandardOutput=append:/home/youruser/yourproject/storage/logs/larago.log
StandardError=append:/home/youruser/yourproject/storage/logs/larago.log

[Install]
WantedBy=multi-user.target
```

### Enable and start service:
```bash
sudo systemctl daemon-reload
sudo systemctl enable larago.service
sudo systemctl start larago.service
```

### Check service status:
```bash
sudo systemctl status larago.service
```

### View logs:
```bash
sudo journalctl -u larago.service -f
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
2. Port conflicts: `lsof -i :8080`
3. Go installation: `go version`
4. Service logs: `sudo journalctl -u larago.service -f`
