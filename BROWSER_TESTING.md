# Browser Testing Guide

## Interactive WebSocket Test Page

LaraGo Socket Package includes an interactive HTML test page to verify your WebSocket engine is working correctly in the browser.

## 🚀 Quick Start

### Step 1: Start the Engine

```bash
php artisan larago:run --background
```

### Step 2: Open Test Page

Open in your browser:
```
http://localhost:8000/larago-test.html
```

Or from your Laravel app public folder:
```
http://your-app.local/larago-test.html
```

### Step 3: Test Connection

The page will automatically:
- Connect to `ws://localhost:8080/ws`
- Display connection status (✅ green = connected)
- Show "Connected! Subscribe to a channel to receive messages"

## 📋 How to Use the Test Page

### Subscribe to a Channel

1. Enter a **Channel Name** (e.g., `orders`, `notifications`, `chat`)
2. Click the **Subscribe** button
3. Status should show: "✅ Connected!"

### Send Test Messages

1. Keep the channel name
2. Enter an **Event Name** (e.g., `MessageSent`, `OrderCreated`)
3. Enter **Message Data** in JSON format:
   ```json
   {
     "user": "John",
     "message": "Hello everyone!",
     "timestamp": "2024-04-20"
   }
   ```
4. Click **Send Message**
5. Message should appear in the "Messages Log" section

## 🔄 Test Broadcasting (Multi-Tab)

To test real-time broadcasting between multiple clients:

### Tab 1 (Sender):
- Subscribe to channel: `broadcast-test`
- Send a message

### Tab 2 (Receiver):
- Open the same test page in another tab
- Subscribe to channel: `broadcast-test`
- When you send from Tab 1, it will **instantly appear in Tab 2**! 

This demonstrates real-time WebSocket broadcasting working perfectly. 🎉

## 📊 What You'll See

### Connection Status
```
🟢 Connected - Ready to send/receive messages
🟠 Connecting - Waiting for WebSocket connection
🔴 Disconnected - Connection lost (auto-reconnects in 3s)
```

### Messages Log Format

**Sent Messages** (Green border):
```
14:23:45
Subscribed to channel: orders
```

**Received Messages** (Blue border):
```
14:23:46
[orders] OrderCreated: {"id":123,"total":99.99}
```

## 🔧 Troubleshooting

### "WebSocket Error: Connection refused"
- Make sure engine is running: `php artisan larago:run --background`
- Engine should listen on `ws://localhost:8080`
- Check port 8080 is not in use: `lsof -i :8080`

### "Not connected to WebSocket" when sending
- Click **Subscribe** first to establish connection
- Wait for status to show "✅ Connected"

### No messages appearing
- Make sure you subscribed to a channel
- Check the Messages Log for any errors
- Open browser console (F12) to see JavaScript errors

### Connection keeps disconnecting
- Check if engine crashed: `pgrep -f "go-engine"`
- Restart engine: `php artisan larago:stop && php artisan larago:run --background`

## 💡 Example Use Cases

### 1. Test Simple Echo
```
Channel: echo
Event: test
Message: {"hello": "world"}
```

### 2. Test Notification System
```
Channel: notifications
Event: UserRegistered
Message: {"user_id": 123, "email": "user@example.com"}
```

### 3. Test Real-time Chat
```
Channel: chat-room-1
Event: message
Message: {"user": "Alice", "text": "Hello!", "timestamp": "2024-04-20T14:30:00Z"}
```

### 4. Test Order Updates
```
Channel: orders
Event: OrderStatusChanged
Message: {"order_id": 456, "status": "shipped", "tracking": "ABC123"}
```

## 🎯 Next Steps

Once you verify the engine works in the browser:

1. **Integrate with Laravel Broadcasting:**
   - Configure your channels in `config/broadcasting.php`
   - Use `broadcast()` helper in your events

2. **Frontend Integration:**
   - Use the same WebSocket connection logic in your app
   - Subscribe to channels you're broadcasting to
   - Handle messages in real-time

3. **Production Deployment:**
   - Use `supervisor` or `systemd` to keep engine running
   - See SUPERVISOR.md for setup instructions

## 📚 More Resources

- [Go Engine Source Code](../go-src/main.go)
- [Artisan Commands](../PUBLISHING.md)
- [Supervisor Setup](../SUPERVISOR.md)
- [GitHub Issues](https://github.com/M-Bilal-4720/Socket/issues)

---

**Happy Testing! 🚀**
