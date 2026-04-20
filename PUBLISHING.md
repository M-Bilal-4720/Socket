# Publishing to GitHub & Installation Guide

## 🚀 Step 1: Push to GitHub

### Create a GitHub Repository

1. Go to [GitHub.com](https://github.com/new)
2. Create new repository named: `larago-socket`
3. Choose "Public" for npm/composer packages
4. **DON'T initialize** with README (we have one)
5. Get your repository URL

### Push to GitHub

```bash
cd /home/malik/Packages/LaraGo

# Verify composer.json is at repository root
ls -la composer.json

# Add remote
git remote add origin https://github.com/M-Bilal-4720/Socket.git

# Push code
git push -u origin master
```

---

## 📦 Step 2: Publish to Packagist

### Option A: Automatic (Recommended)

1. Go to [Packagist.org](https://packagist.org)
2. Click "Submit Package"
3. Enter: `https://github.com/M-Bilal-4720/Socket.git`
4. Click "Check"
5. It will auto-update when you push to GitHub

### Option B: Manual Updates

After pushing to GitHub, Packagist automatically detects updates.

---

## 🔧 Step 3: Installation for Users

### For Laravel Projects

```bash
# Via Composer (after it's on Packagist)
composer require larago/socket

# Or from GitHub VCS repository
composer config repositories.socket vcs https://github.com/M-Bilal-4720/Socket.git
composer require larago/socket:dev-master --prefer-source
```

### What Happens During Installation

1. ✅ Package files copied to `vendor/larago/socket/`
2. ✅ Service Provider auto-discovered by Laravel
3. ✅ `src/GoSocketServiceProvider.php` registered automatically
4. ⚠️ **Manual Step Required** (for local/symlinked repos): Compile Go binary:
   ```bash
   cd vendor/larago/socket
   bash build.sh
   ```
   This creates `bin/go-engine` (~7.4MB)

### Complete Installation Commands

```bash
cd /path/to/your/laravel/project

# (Optional) Add VCS repository if not on Packagist yet
composer config repositories.socket vcs https://github.com/M-Bilal-4720/Socket.git

# Install package
composer require larago/socket:dev-master --prefer-source

# Compile Go engine (required step)
cd vendor/larago/socket && bash build.sh && cd ../../../

# Configure in .env
echo "BROADCAST_DRIVER=larago" >> .env

# Update config/broadcasting.php:
# 'larago' => [
#     'driver' => 'larago',
# ],

# Start the Go engine
vendor/larago/socket/bin/go-engine
```

---

## 📝 Usage After Installation

### Create an Event

```php
php artisan make:event MessageSent
```

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcast
{
    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new Channel('chat');
    }
}
```

### Broadcast from Controller

```php
use App\Events\MessageSent;

broadcast(new MessageSent('Hello World'));
```

### Frontend Integration

```html
<!DOCTYPE html>
<html>
<head>
    <title>LaraGo Real-time Chat</title>
</head>
<body>
    <div id="messages"></div>
    <input type="text" id="messageInput" placeholder="Type a message...">
    <button onclick="sendMessage()">Send</button>

    <script>
        const ws = new WebSocket('ws://localhost:8080/ws');
        
        ws.onopen = () => {
            console.log('Connected to LaraGo Engine');
            // Subscribe to channel
            ws.send(JSON.stringify({
                event: 'subscribe',
                channel: 'chat'
            }));
        };

        ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            console.log('Received:', data);
            
            // Add message to DOM
            const messagesDiv = document.getElementById('messages');
            const messageEl = document.createElement('div');
            messageEl.textContent = data.data.message || data.message;
            messagesDiv.appendChild(messageEl);
        };

        function sendMessage() {
            const input = document.getElementById('messageInput');
            // This triggers the broadcast from Laravel
            fetch('/api/send-message', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: input.value })
            });
            input.value = '';
        }
    </script>
</body>
</html>
```

---

## 🔗 Repository Structure

```
GitHub Repository: https://github.com/M-Bilal-4720/Socket
Branch: master

Repository Root Layout:
├── composer.json          ← NOW AT ROOT (was in Socket/)
├── src/                   ← Laravel Service Provider & Broadcaster
│   ├── GoSocketServiceProvider.php
│   └── GoBroadcaster.php
├── go-src/                ← Go WebSocket engine
│   ├── main.go
│   ├── go.mod
│   └── go.sum
├── build.sh               ← Compile Go engine
├── bin/                   ← Compiled binary location
│   └── go-engine          ← 7.4MB executable
└── README.md
```

---

## ✨ Features Included

✅ Laravel Service Provider auto-registered  
✅ Auto-discoverable by Laravel's package discovery  
✅ Unix socket (`/tmp/larago.sock`) for PHP → Go communication  
✅ WebSocket server on port `:8080` for client connections  
✅ Channel-based broadcasting with subscription tracking  
✅ Real-time client subscriptions and message routing  
✅ Go module system for dependency management  
✅ Self-contained compilation via `build.sh`

## ⚠️ Important Notes

- **Go Installation Required**: Ensure Go 1.22+ is installed on the system
- **Manual Compilation**: For development installs, run `bash build.sh` in package root
- **Port 8080**: Go engine listens on WebSocket port 8080 (configurable in `main.go`)
- **Unix Socket**: Laravel broadcasts to `/tmp/larago.sock` (configurable in `GoBroadcaster.php`)  
