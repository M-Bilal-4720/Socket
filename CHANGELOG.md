# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2024-04-20

### Added
- `--force` flag to `larago:run` command for automatic restart
- Improved Unix socket detection using `lsof`
- Better error messages when socket is already in use
- Automatic stale socket file cleanup

### Improved
- More reliable socket availability detection
- Graceful handling of engine restart scenarios
- User-friendly error messages with actionable solutions

### Fixed
- Race condition when killing existing engine processes
- Stale socket files preventing new engine startup
- Panic errors now caught and displayed as helpful messages

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
