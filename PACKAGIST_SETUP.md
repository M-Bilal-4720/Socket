# Publishing to Packagist

LaraGo Socket Package is ready to be published on Packagist (the PHP package repository).

## Current Status

✅ **Package is Packagist-ready!**

- [x] composer.json with all required metadata
- [x] MIT License file
- [x] README.md with clear installation instructions
- [x] CHANGELOG.md documenting versions
- [x] SECURITY.md with security policy
- [x] .gitattributes for clean distribution
- [x] GitHub repository: https://github.com/M-Bilal-4720/Socket
- [x] Version tags (v1.0.0, v1.1.0, v1.2.0, v2.0.0)

## How to Publish

### Step 1: Create Packagist Account
1. Go to https://packagist.org
2. Sign up or log in with GitHub
3. Click "Submit Package"

### Step 2: Submit Package
1. Enter repository URL: `https://github.com/M-Bilal-4720/Socket`
2. Click "Check" to verify it's readable
3. Click "Submit"

### Step 3: Enable GitHub Hook (Automatic Updates)
1. Go to your package page on Packagist
2. Click "Edit Package"
3. In GitHub section, authorize Packagist
4. Packagist will automatically update when you push to GitHub

### Step 4: Publish a New Release (Update Existing Package)

Run these commands from the package repository root:

```bash
git add src/GoBroadcaster.php src/Console/Commands/LaraGoRunCommand.php CHANGELOG.md
git commit -m "fix: Laravel discovery compatibility and Windows process handling"
git push origin master

# Tag the new Packagist version
git tag v2.0.5
git push origin v2.0.5
```

Then force a refresh in Packagist:
1. Open https://packagist.org/packages/larago/socket
2. Click "Update" (or wait for webhook auto-sync)

## Installation Instructions for Users

Once published on Packagist, users can simply run:

```bash
composer require larago/socket
php artisan larago:run
```

**No manual build needed!** The Artisan command automatically builds the Go engine on first run.

## Verify Package Info

**Package Name:** `larago/socket`

**Description:** Real-time WebSocket Broadcasting Engine for Laravel with Go Backend

**Keywords:** laravel, websocket, broadcasting, real-time, go, golang

**Supported Laravel Versions:** 8.0+, 9.0+, 10.0+, 11.0+, 12.0+, 13.0+

**License:** MIT

**Maintainer:** Malik Bilal (malik.bilal4720@gmail.com)

## Commands After Publishing

Users can run the Go engine with:

```bash
# Default (0.0.0.0:8080)
php artisan larago:run

# Custom port
php artisan larago:run --port=9000

# Custom host
php artisan larago:run --host=127.0.0.1
```

## Repository Links

- GitHub: https://github.com/M-Bilal-4720/Socket
- Issues: https://github.com/M-Bilal-4720/Socket/issues
- Current Packagist: https://packagist.org/packages/larago/socket (when published)

## Next Steps

1. Visit https://packagist.org and submit this repository
2. Enable GitHub webhook for automatic updates
3. Share the package with the Laravel community!

---

For any issues or questions, visit: https://github.com/M-Bilal-4720/Socket/issues
