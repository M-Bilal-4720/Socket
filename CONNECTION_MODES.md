# LaraGo Socket - Connection Modes & Security

## Overview

LaraGo Socket now supports two connection modes:

1. **Public Mode (Default)** - No authentication required
2. **Private Mode** - JWT token authentication required

Additionally, you can configure the host and port for testing on different ports and interfaces.

## Connection Modes

### Public Mode (Default)

Anyone can connect to the WebSocket server without authentication.

```bash
# Start engine in public mode (default)
php artisan larago:run --background

# Or explicitly specify public mode
php artisan larago:run --mode=public --background
```

**Use Case:** Development, internal networks, or when you don't need to restrict access.

### Private Mode

Clients must provide a valid JWT token to establish a connection.

```bash
# Start engine in private mode
php artisan larago:run --mode=private --background
```

When connecting in private mode:
- Clients must include a JWT token in the connection URL: `ws://localhost:8080/ws?token=YOUR_JWT_TOKEN`
- Invalid or missing tokens will be rejected with an error message
- Expired tokens are not validated by the Go engine (server-side token validation recommended)

## JWT Token Generation

### Generate Test Token

Use the `larago:token` command to generate a JWT token:

```bash
# Generate token with default user ID (1) and 1 hour expiration
php artisan larago:token

# Generate token for specific user with custom expiration
php artisan larago:token --user-id=42 --expires=7200
```

**Output:**
```
✅ JWT Token generated successfully!

Token: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NzY2NzI0MjQsImV4cCI6MTc3NjY3NjAyNCwidXNlcl9pZCI6IjEiLCJhcHAiOiJsYXJhZ28ifQ==.DTICKV5nmiQGsnlKfAD5OwNcYmOplbAFEfATb1VJlwE

Token Details:
  • User ID: 1
  • Expires in: 3600 seconds
  • Expires at: 2026-04-20 09:07:04
```

### Token Structure

Generated tokens are standard JWT tokens with:
- **Header:** `{"typ": "JWT", "alg": "HS256"}`
- **Payload:**
  - `iat` - Issued at (Unix timestamp)
  - `exp` - Expiration time (Unix timestamp)
  - `user_id` - User identifier
  - `app` - Always set to "larago"
- **Signature:** HMAC-SHA256 using Laravel's `APP_KEY`

### Token Verification

The Go engine validates JWT token structure (must have 3 parts separated by dots). For production:
1. Implement server-side token validation on the Laravel side
2. Verify token expiration and signature
3. Use the `user_id` claim to identify the connected user

## Port Configuration

### Configurable Port

Run the engine on any available port:

```bash
# Run on port 9000
php artisan larago:run --port=9000 --background

# Run on a custom host and port
php artisan larago:run --host=127.0.0.1 --port=3000 --background
```

**Environment Variables:**
```bash
export LARAGO_PORT=9000
export LARAGO_HOST=0.0.0.0
php artisan larago:run --background
```

### Using with Test Page

The browser test page at `http://localhost:8000/larago-test` now includes:
- **Host Input** - Change connection host
- **Port Input** - Change connection port (test on different ports)
- **Connection Mode Selector** - Choose between Public and Private
- **JWT Token Input** - Paste token when using Private mode

## Testing Guide

### Test 1: Public Mode (Default)

1. Start engine:
   ```bash
   php artisan larago:run --background
   ```

2. Open test page: `http://localhost:8000/larago-test`

3. Should show: **✅ Connected! Subscribe to a channel to receive messages.**

4. Enter a channel name and click Subscribe

### Test 2: Private Mode with JWT

1. Generate a token:
   ```bash
   php artisan larago:token
   ```
   Copy the generated token

2. Start engine in private mode:
   ```bash
   php artisan larago:run --mode=private --background
   ```

3. Open test page: `http://localhost:8000/larago-test`

4. Change settings:
   - Set **Connection Mode** to "Private"
   - Paste the JWT token in the **JWT Token** field
   - Keep Port as 8080 (or match your engine's port)

5. Click Subscribe - should show: **✅ Connected!**

### Test 3: Custom Port

1. Start engine on custom port:
   ```bash
   php artisan larago:run --port=9000 --background
   ```

2. Open test page: `http://localhost:8000/larago-test`

3. Change **Port** to 9000

4. Click Subscribe - should connect to the engine on port 9000

### Test 4: Private Mode on Custom Port

1. Generate token:
   ```bash
   php artisan larago:token --user-id=99 --expires=1800
   ```

2. Start engine:
   ```bash
   php artisan larago:run --mode=private --port=7000 --background
   ```

3. Open test page and set:
   - Port: 7000
   - Connection Mode: Private
   - JWT Token: (paste the generated token)

4. Click Subscribe and test messaging

## Troubleshooting

### Connection Failed - Private Mode
- **Issue:** "Missing authentication token" or "Invalid authentication token"
- **Solution:** 
  - Verify you're in Private mode
  - Ensure token is correctly pasted in JWT Token field
  - Generate a new token: `php artisan larago:token`

### Connection Failed - Wrong Port
- **Issue:** "WebSocket connection failed" after setting custom port
- **Solution:**
  - Verify engine is running on that port: `lsof -i :PORT_NUMBER`
  - Ensure host matches (usually `localhost` or `127.0.0.1`)
  - Restart engine if port was changed

### Port Already in Use
- **Issue:** "Address already in use" error
- **Solution:**
  ```bash
  # Kill existing engine
  pkill -f "go-engine"
  
  # Or use --force flag
  php artisan larago:run --port=8080 --force --background
  ```

## Environment Variables

```bash
# Set connection mode
LARAGO_CONNECTION_MODE=private    # or "public"

# Set host and port
LARAGO_HOST=0.0.0.0
LARAGO_PORT=8080

# JWT secret (auto-uses Laravel's APP_KEY)
LARAGO_JWT_SECRET=your-secret-key
```

## Production Recommendations

For production deployments:

1. **Use Private Mode**
   ```bash
   php artisan larago:run --mode=private --host=127.0.0.1 --background
   ```

2. **Enable HTTPS/WSS**
   - Configure Nginx/Apache to reverse proxy WebSocket connections to `ws://localhost:8080/ws`
   - Use `wss://` (WebSocket Secure) in production

3. **Implement Token Validation**
   - Add server-side token verification
   - Check token expiration before issuing
   - Map tokens to authenticated users

4. **Monitor Connections**
   - Log connection attempts (token validation success/failure)
   - Monitor port availability and process health
   - Use supervisor or systemd to keep engine running

## API Reference

### Command Options

```bash
php artisan larago:run [OPTIONS]

OPTIONS:
  --host=HOSTNAME           Host to listen on (default: 0.0.0.0)
  --port=PORT              Port to listen on (default: 8080)
  --mode=MODE              Connection mode: public or private (default: public)
  --background             Run in background mode
  --force                  Kill existing engine and start fresh
```

### Token Command

```bash
php artisan larago:token [OPTIONS]

OPTIONS:
  --user-id=ID             User ID for the token (default: 1)
  --expires=SECONDS        Token expiration time in seconds (default: 3600)
```

### WebSocket Connection String

```javascript
// Public mode
const ws = new WebSocket('ws://localhost:8080/ws');

// Private mode with token
const token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...';
const ws = new WebSocket(`ws://localhost:8080/ws?token=${token}`);

// Custom host and port
const ws = new WebSocket(`ws://custom.host:9000/ws?token=${token}`);
```

## Version History

### v1.3.0 (2026-04-20)
- Added Public/Private connection modes
- Added JWT token generation command
- Made port and host configurable
- Updated test page with configuration inputs
- Added this documentation

### Previous Versions
See [CHANGELOG.md](CHANGELOG.md) for earlier versions.
