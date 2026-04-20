# Windows Quick Fix Guide

## ✅ Your Windows Issues Are Now Fixed!

The GitHub repo has been updated with Windows compatibility fixes. Here's what you need to do:

## 🔧 Step 1: Update LaraGo Package

```bash
composer update larago/socket
```

This will get the latest Windows-compatible version.

## 🏗️ Step 2: Build the Go Engine

You have two options:

### Option A: Using build.bat (Recommended for Windows)

```bash
cd vendor/larago/socket
build.bat
```

This will create `bin/go-engine.exe` (the Windows binary)

### Option B: Using PowerShell with build.sh

If build.bat doesn't work, try bash:

```bash
cd vendor/larago/socket
bash build.sh
```

## 🚀 Step 3: Run the Engine

### Foreground Mode (for testing):
```bash
php artisan larago:run
```

This will run the engine in the current terminal. Press `Ctrl+C` to stop.

### Background Mode (for production):
```bash
php artisan larago:run --background
```

This runs the engine in the background. You can close the terminal.

## ✨ What Was Fixed

| Issue | Fix |
|-------|-----|
| **TTY mode not supported on Windows** | ✅ Now detects Windows and skips TTY setup |
| **Binary not found** | ✅ Auto-builds binary if missing |
| **Wrong binary extension** | ✅ Automatically adds `.exe` on Windows |
| **Path handling** | ✅ Properly handles Windows paths |

## 🛠️ Troubleshooting

### Issue: "Go is not installed"

1. Download Go from: https://golang.org/dl/
2. Choose **go1.22.5.windows-amd64.msi** or newer
3. Install and restart your terminal/PowerShell
4. Verify: `go version`

### Issue: "build.bat not found"

The build.bat should be in the package. If missing:

```bash
cd vendor/larago/socket
bash build.sh
```

### Issue: Port 8080 already in use

Find and kill the process:

**PowerShell (As Admin):**
```powershell
netstat -ano | findstr :8080
Stop-Process -Id <PID> -Force
```

**Or use a different port:**
```bash
php artisan larago:run --port=9000
```

### Issue: "Engine failed to start in background"

1. Check if Go is installed: `go version`
2. Check the logs:
   ```bash
   type storage\logs\larago-engine.log
   ```
3. Try foreground mode first:
   ```bash
   php artisan larago:run
   ```

## 📋 Command Reference

```bash
# Start in foreground (for testing)
php artisan larago:run

# Start in background (for production)
php artisan larago:run --background

# Use a different port
php artisan larago:run --port=9000

# Force kill any existing engine and start fresh
php artisan larago:run --force

# Stop the running engine gracefully
php artisan larago:stop

# Force stop the engine
php artisan larago:stop --force

# Generate JWT token for private channels
php artisan larago:token --user-id=1
```

## 🌐 Test the Engine

Once running, open your browser:

```
http://localhost:8000/larago-test
```

(Or replace port 8000 with your Laravel dev server port)

### Test Public Channel:
1. Channel: `public-chat`
2. Click "Subscribe to Public Channel"
3. Should see ✅ subscribed

### Test Private Channel:
1. Generate token: `php artisan larago:token`
2. Channel: `private-user-1`
3. Paste the token
4. Click "Subscribe to Private Channel"
5. Should see ✅ subscribed

## 💡 Pro Tips

### Run in PowerShell (Better than CMD)

PowerShell has better color support and output. Use Windows Terminal for best experience:

```powershell
# Windows Terminal
pwsh

# Then run commands
php artisan larago:run --background
```

### Set Custom Environment Variables

Edit your `.env` file:

```env
LARAGO_HOST=0.0.0.0
LARAGO_PORT=8080
```

## 📞 Getting Help

If something still doesn't work:

1. **Check Go installation:**
   ```bash
   go version
   ```

2. **Check binary exists:**
   ```bash
   dir vendor\larago\socket\bin\
   ```
   Should show: `go-engine.exe`

3. **Check logs:**
   ```bash
   type storage\logs\larago-engine.log
   ```

4. **Try manual build:**
   ```bash
   cd vendor\larago\socket
   go build -o bin\go-engine.exe go-src\main.go
   ```

5. **Check port is free:**
   ```bash
   netstat -ano | findstr :8080
   ```

## ✅ Expected Output

After `php artisan larago:run --background`:

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

That's it! Your LaraGo engine is now running on Windows! 🎉
