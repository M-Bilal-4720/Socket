@echo off
REM Build LaraGo Socket Go Engine on Windows
REM This script compiles the Go WebSocket engine binary

echo.
echo Building LaraGo Socket Go Engine...
echo.

REM Change to script directory
cd /d "%~dp0"

REM Check if Go is installed
where go >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo Error: Go is not installed or not in PATH
    echo Please install Go from: https://golang.org/dl/
    echo Then verify with: go version
    exit /b 1
)

REM Create bin directory if it doesn't exist
if not exist "bin" (
    echo Creating bin directory...
    mkdir bin
)

REM Download dependencies
echo Downloading Go dependencies...
pushd go-src
go mod download
if %ERRORLEVEL% NEQ 0 (
    popd
    echo Failed to download dependencies
    exit /b 1
)

REM Compile for Windows
echo Compiling Go engine for Windows...
go build -o ..\bin\go-engine.exe main.go
popd
if %ERRORLEVEL% NEQ 0 (
    echo Build failed
    exit /b 1
)

echo.
echo Build successful
echo Binary: %~dp0bin\go-engine.exe
exit /b 0
