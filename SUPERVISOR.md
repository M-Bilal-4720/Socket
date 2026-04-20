# Supervisor Configuration for LaraGo Socket Engine

Supervisor is a process control system that can keep the LaraGo engine running in the background and automatically restart it if it crashes.

## Installation

### Ubuntu/Debian
```bash
sudo apt-get install supervisor
```

### macOS
```bash
brew install supervisor
```

## Setup Instructions

### Step 1: Create Supervisor Configuration File

Create `/etc/supervisor/conf.d/larago.conf`:

```ini
[program:larago-engine]
process_name=%(program_name)s
command=php /path/to/your/laravel/artisan larago:run
autostart=true
autorestart=true
stderr_logfile=/var/log/larago-error.log
stdout_logfile=/var/log/larago-output.log
numprocs=1
redirect_stderr=true
```

**Replace `/path/to/your/laravel/` with your actual Laravel project path.**

### Step 2: Update and Start Supervisor

```bash
# Reread configuration files
sudo supervisorctl reread

# Update supervisor with new configuration
sudo supervisorctl update

# Start the larago-engine process
sudo supervisorctl start larago-engine

# Verify it's running
sudo supervisorctl status
```

## Common Commands

```bash
# Check status of all processes
sudo supervisorctl status

# Start the engine
sudo supervisorctl start larago-engine

# Stop the engine
sudo supervisorctl stop larago-engine

# Restart the engine
sudo supervisorctl restart larago-engine

# View logs
tail -f /var/log/larago-output.log
tail -f /var/log/larago-error.log
```

## Alternative: Using Artisan Command (Recommended)

For simpler setup without Supervisor, use the built-in background mode:

```bash
# Start in background
php artisan larago:run --background

# Stop the engine
php artisan larago:stop
```

## System Service (Systemd)

For modern systemd-based systems, create `/etc/systemd/system/larago.service`:

```ini
[Unit]
Description=LaraGo WebSocket Engine
After=network.target
After=mysql.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/your/laravel
ExecStart=/usr/bin/php /path/to/your/laravel/artisan larago:run
Restart=always
RestartSec=5
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

Then:
```bash
sudo systemctl daemon-reload
sudo systemctl enable larago.service
sudo systemctl start larago.service
sudo systemctl status larago.service
```

## Troubleshooting

### Port Already in Use
```bash
# Find and kill process using port 8080
lsof -i :8080
kill -9 <PID>
```

### Socket Already in Use
```bash
# Remove stale socket
rm /tmp/larago.sock
sudo supervisorctl restart larago-engine
```

### Check Logs
```bash
tail -f /var/log/larago-output.log
tail -f /var/log/larago-error.log
```
