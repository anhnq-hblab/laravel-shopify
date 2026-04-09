# Docker Testing Environment

Multi-version PHP/Laravel testing setup for Laravel Shopify package.

## Quick Start

### Run all tests
```bash
cd docker-testing
./run-tests.sh
```

### Test specific PHP version
```bash
./run-tests.sh 8.3
```

### Test specific PHP + Laravel combination
```bash
./run-tests.sh 8.3 11.0
```

## Using Docker Compose

### Run specific service
```bash
docker-compose up php83-laravel13
```

### Run all services in parallel
```bash
docker-compose up --parallel
```

### Clean up
```bash
docker-compose down
```

## Test Matrix

| PHP | Laravel 10 | Laravel 11 | Laravel 12 | Laravel 13 |
|-----|------------|------------|------------|------------|
| 8.1 | ✅         | ✅         | ✅         | ❌ (skip)  |
| 8.2 | ✅         | ✅         | ✅         | ✅         |
| 8.3 | -          | ✅         | ✅         | ✅         |
| 8.4 | -          | ✅         | ❌ (skip)  | ✅         |

## Available Dockerfiles

- `Dockerfile.php81` - PHP 8.1
- `Dockerfile.php82` - PHP 8.2
- `Dockerfile.php83` - PHP 8.3
- `Dockerfile.php84` - PHP 8.4

## CI Integration

These tests mirror the GitHub Actions CI matrix defined in `.github/workflows/ci.yml`.
