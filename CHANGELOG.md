# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2024-04-20

### Added
- Background mode: `php artisan larago:run --background` (starts engine without blocking)
- Stop command: `php artisan larago:stop` (gracefully stops the running engine)
- Force flag: `php artisan larago:run --force` (kills existing engine and starts fresh)
- Graceful shutdown handling in Go engine (SIGINT/SIGTERM)
- Automatic socket file cleanup on startup
- Supervisor configuration guide (SUPERVISOR.md)
- Systemd service file documentation
- Better error messages for socket conflicts
- Go engine improvements: signal handling, stale socket cleanup

### Changed
- Go engine now properly handles graceful shutdown
- Improved socket management - removes stale socket files automatically
- Better process management in Artisan commands

### Fixed
- Socket "address already in use" errors now properly handled
- Engine crashes no longer leave stale socket files
- Graceful shutdown on Ctrl+C and signals

## [1.1.0] - 2024-04-20

### Added
- Auto-build feature: Go engine binary is automatically compiled on first run
- `php artisan larago:run` command with `--host` and `--port` options
- Comprehensive Packagist metadata (keywords, homepage, support information)
- LICENSE file (MIT)
- .gitattributes for clean distribution

### Changed
- Updated installation instructions - package now installable from Packagist
- Improved README documentation with simplified installation steps
- Enhanced composer.json with Laravel framework dependency

### Fixed
- Users no longer need to manually run `bash build.sh`

## [1.0.0] - 2024-04-20

### Added
- Initial release of LaraGo Socket Package
- Go real-time WebSocket engine with Unix socket support
- Laravel Broadcasting integration via GoBroadcaster
- Service Provider for auto-discovery
- build.sh script for compiling Go engine
- Comprehensive documentation

### Features
- WebSocket server on port 8080 for client connections
- Unix socket listener at /tmp/larago.sock for Laravel communication
- Support for Laravel 8, 9, 10, 11, and 13
- Simple JSON protocol for message broadcasting

[1.2.0]: https://github.com/M-Bilal-4720/Socket/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/M-Bilal-4720/Socket/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/M-Bilal-4720/Socket/releases/tag/v1.0.0
