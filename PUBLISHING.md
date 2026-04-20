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
cd /home/malik/Packages/LaraGo/Socket

# Add remote
git remote add origin https://github.com/M-Bilal-4720/Socket.git

# Rename branch to main (optional but recommended)
git branch -M main

# Push code
git push -u origin main
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

# Or from GitHub (immediate)
composer require M-Bilal-4720/larago:dev-main
```

### What Happens During Installation

1. ✅ Package files copied
2. ✅ Composer scripts run `build.sh`
3. ✅ Go dependencies downloaded automatically
4. ✅ Go engine compiled automatically
5. ✅ Binary placed in `vendor/larago/socket/bin/go-engine`

### Complete Installation Commands

```bash
cd /path/to/your/laravel/project

# Install package
composer require larago/socket

# Configure in .env
echo "BROADCAST_DRIVER=larago" >> .env

# In config/broadcasting.php, add:
# 'larago' => [
#     'driver' => 'larago',
# ],

# Start the engine
php artisan tinker
>>> shell('vendor/larago/socket/bin/go-engine')
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

## 🔗 Current Git Status

```bash
cd /home/malik/Packages/LaraGo/Socket
git status
```

---

## ✨ Features Included

✅ Auto-build Go binary via composer scripts  
✅ Laravel Service Provider auto-registered  
✅ Unix socket for internal communication  
✅ WebSocket server on port 8080  
✅ Channel-based broadcasting  
✅ Real-time client subscriptions  
