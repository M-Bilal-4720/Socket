# Security Policy

## Supported Versions

We support the following versions with security updates:

| Version | Supported          |
| ------- | ------------------ |
| 1.1.x   | ✅ Actively maintained |
| 1.0.x   | ✅ Supported       |

## Reporting a Vulnerability

If you discover a security vulnerability in LaraGo Socket Package, please email **malik.bilal4720@gmail.com** instead of using the issue tracker.

Please include:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if available)

We will:
- Acknowledge your report within 48 hours
- Investigate and assess the vulnerability
- Develop a fix
- Release a security update
- Credit you (if you wish) for the responsible disclosure

## Security Best Practices

When using LaraGo Socket Package:

1. **Keep dependencies updated** - Regularly update Laravel, Go, and PHP
2. **Validate all inputs** - Always validate WebSocket messages on both client and server
3. **Use HTTPS/WSS** - Use secure WebSocket connections in production (wss:// instead of ws://)
4. **Environment variables** - Store sensitive configuration in .env, never commit to repository
5. **Access control** - Implement proper authentication and authorization for broadcast channels

## Supported Laravel Versions

- Laravel 8.0+
- Laravel 9.0+
- Laravel 10.0+
- Laravel 11.0+
- Laravel 13.0+

## Go Version Requirements

- Go 1.16 or higher

## PHP Version Requirements

- PHP 7.4 or higher
