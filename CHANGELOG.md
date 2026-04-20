# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2024-12-XX

### Added
- **Full Cross-Platform Support**: Windows, macOS, and Linux with unified codebase
- **Laravel 12 Support**: Now supports Laravel 8-13 (including new Laravel 12)
- **Channel-Level Access Control**: Public and private channels with JWT authentication
- **TCP Socket Communication**: Replaced Unix sockets with TCP for cross-platform compatibility
- **Windows Setup Guide**: Comprehensive WINDOWS_SETUP.md with troubleshooting
- **Windows Quick Fix Guide**: WINDOWS_QUICK_FIX.md for common issues
- **macOS Setup Guide**: MACOS_SETUP.md with Homebrew and LaunchAgent configuration
- **Linux Setup Guide**: LINUX_SETUP.md with Systemd service configuration
- **Enhanced .gitattributes**: Proper file distribution rules for Packagist
- **Improved composer.json**: Better metadata with keywords, support links, and readme field
- **Interactive Test Page**: larago-test.html with public/private channel testing and type badges
- **Token Generation Command**: `php artisan larago:token` for generating JWT tokens
- **Build Scripts**: Enhanced build.bat with detailed error messages for Windows
- **API Documentation**: Comprehensive comments in Go engine source code

### Changed
- **Architecture**: Moved from Unix socket (/tmp/larago.sock) to TCP socket (127.0.0.1:6001)
- **Windows Support**: Go engine now properly runs on Windows (builds to go-engine.exe)
- **Process Management**: Platform-aware process detection and termination (Windows: tasklist/taskkill, Unix: pgrep/pkill)
- **TTY Mode Handling**: Conditional TTY mode that works correctly on Windows
- **Background Execution**: Platform-specific background process launching
- **Build System**: Unified build.sh for Unix and build.bat for Windows
- **Package Metadata**: Enhanced description and keywords for better Packagist discoverability
- **Documentation**: Restructured README with table of contents and badges

### Fixed
- **Windows Compatibility**: Fixed RuntimeException "TTY mode is not supported on Windows platform"
- **Cross-Platform Sockets**: Replaced Unix-only sockets with platform-agnostic TCP
- **Windows Process Signals**: Proper use of taskkill instead of pkill on Windows
- **Build Script Errors**: Better error handling and messages in build.bat
- **JWT Validation**: Fixed token validation to check 3-part format properly
- **GoBroadcaster**: Updated to use TCP sockets instead of Unix sockets

### Removed
- Unix socket dependency (/tmp/larago.sock) - now uses TCP
- Platform-specific filesystem dependencies
- Requirement to manually configure socket paths

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

[2.0.0]: https://github.com/M-Bilal-4720/Socket/compare/v1.2.0...v2.0.0
[1.2.0]: https://github.com/M-Bilal-4720/Socket/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/M-Bilal-4720/Socket/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/M-Bilal-4720/Socket/releases/tag/v1.0.0
