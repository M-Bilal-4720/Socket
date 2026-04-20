@echo off
REM Build LaraGo Socket Go Engine on Windows

echo.
echo 🔨 Building LaraGo Socket Go Engine...
echo.

cd /d "%~dp0"

REM Check if Go is installed
where go >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Error: Go is not installed or not in PATH
    echo Please install Go from https://golang.org/dl/
    pause
    exit /b 1
)

REM Create bin directory if it doesn't exist
if not exist "bin" mkdir bin

REM Download dependencies
echo 📦 Downloading Go dependencies...
go mod download

REM Compile for current platform (Windows)
echo ⚙️  Compiling Go engine...
go build -o bin\go-engine.exe go-src\main.go

if %ERRORLEVEL% EQU 0 (
    echo ✅ Build successful!
    echo 📁 Binary: %~dp0bin\go-engine.exe
) else (
    echo ❌ Build failed!
    pause
    exit /b 1
)

echo.
