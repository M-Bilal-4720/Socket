#!/bin/bash
# Auto-build Go engine during composer installation

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
GO_SRC="$SCRIPT_DIR/go-src"
BIN_DIR="$SCRIPT_DIR/bin"

echo "🔨 Building LaraGo Socket Go Engine..."

# Create bin directory if it doesn't exist
mkdir -p "$BIN_DIR"

# Check if go is installed
if ! command -v go &> /dev/null; then
    echo "❌ Go is not installed. Please install Go from https://golang.org/dl/"
    exit 1
fi

# Navigate to go-src directory
cd "$SCRIPT_DIR" || exit 1

# Initialize go.mod if it doesn't exist
if [ ! -f go.mod ]; then
    echo "📦 Initializing Go module..."
    go mod init larago/socket || true
fi

# Download/update dependencies
echo "📦 Downloading Go dependencies..."
go mod tidy

# Build the binary
echo "⚙️  Compiling Go engine..."
cd "$GO_SRC" || exit 1
go build -o "$BIN_DIR/go-engine" main.go

if [ -f "$BIN_DIR/go-engine" ]; then
    chmod +x "$BIN_DIR/go-engine"
    echo "✅ Build successful! Binary: $BIN_DIR/go-engine"
else
    echo "❌ Build failed!"
    exit 1
fi
