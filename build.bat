@echo off
REM Build LaraGo Socket Go Engine on Windows
REM This script compiles the Go WebSocket engine binary

echo.
echo 🔨 Building LaraGo Socket Go Engine...
echo.

REM Change to script directory
cd /d "%~dp0"

REM Check if Go is installed
where go >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Error: Go is not installed or not in PATH
    echo.
    echo Please install Go from: https://golang.org/dl/
    echo Choose: go1.22.5.windows-amd64.msi (or latest version)
    echo.
    echo After installation:
    echo 1. Restart your terminal/PowerShell
    echo 2. Verify: go version
    echo 3. Run this script again
    echo.
    pause
    exit /b 1
)

REM Create bin directory if it doesn't exist
if not exist "bin" (
    echo 📁 Creating bin directory...
    mkdir bin
)

REM Download dependencies
echo 📦 Downloading Go dependencies...
call go mod download
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Failed to download dependencies
    pause
    exit /b 1
)

REM Compile for Windows
echo ⚙️  Compiling Go engine for Windows...
call go build -o bin\go-engine.exe go-src\main.go
if %ERRORLEVEL% EQU 0 (
    echo.
    echo ✅ Build successful!
    echo 📁 Binary: %~dp0bin\go-engine.exe
    echo.
    echo Next steps:
    echo 1. Run: php artisan larago:run --background
    echo 2. Open: http://localhost:8000/larago-test
    echo.
) else (
    echo ❌ Build failed!
    echo.
    echo Troubleshooting:
    echo - Make sure Go is installed: go version
    echo - Check PATH includes Go: go env GOROOT
    echo - Try running from PowerShell as Administrator
    echo.
    pause
    exit /b 1
)

echo.
pause
