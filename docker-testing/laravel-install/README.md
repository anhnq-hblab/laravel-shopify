# Laravel Installation Testing

Test the Laravel Shopify package with fresh Laravel installations.

## Purpose

This setup tests the complete installation flow:
1. Create fresh Laravel project
2. Install the Shopify package
3. Publish configuration
4. Verify everything works

## Requirements

- Docker
- Docker Compose

## Usage

### Test all Laravel versions
```bash
cd docker-testing/laravel-install
./test-install.sh
```

### Test specific Laravel version
```bash
./test-install.sh 11  # Test Laravel 11 only
```

### Using Docker Compose directly
```bash
# Build and run specific version
docker-compose up laravel11

# Run in background
docker-compose up -d laravel11

# Check logs
docker-compose logs laravel11

# Stop
docker-compose down
```

## Test Matrix

| Laravel | PHP | Test Command |
|---------|-----|--------------|
| 10.x | 8.2 | `./test-install.sh 10` |
| 11.x | 8.3 | `./test-install.sh 11` |
| 12.x | 8.3 | `./test-install.sh 12` |
| 13.x | 8.4 | `./test-install.sh 13` |

## What It Tests

1. **Fresh Laravel Installation**
   - Creates new Laravel project via composer
   - Sets up proper directory structure

2. **Package Installation**
   - Adds local package repository
   - Installs kyon147/laravel-shopify
   - Resolves dependencies

3. **Configuration**
   - Publishes shopify-app.php config
   - Verifies config structure

4. **Basic Functionality**
   - Checks routes are registered
   - Runs package tests

## Troubleshooting

### Build fails
```bash
# Clean build
docker-compose down --volumes --remove-orphans
docker-compose build --no-cache laravel11
```

### Check installed packages
```bash
docker exec shopify-laravel11 composer show | grep shopify
```

### Check config
```bash
docker exec shopify-laravel11 cat config/shopify-app.php
```

### Shell access
```bash
docker exec -it shopify-laravel11 bash
```

## Files

- `Dockerfile.laravel{10,11,12,13}` - Docker images for each Laravel version
- `docker-compose.yml` - Compose configuration
- `test-install.sh` - Automated testing script
