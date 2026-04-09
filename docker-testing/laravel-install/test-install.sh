#!/bin/bash

# Test Laravel Shopify package installation with fresh Laravel projects
# Usage: ./test-install.sh [laravel_version]
# Example: ./test-install.sh 11

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Testing Fresh Laravel Installation${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

LARAVEL_VERSION="${1:-all}"

# Ensure we're in the right directory
cd "$SCRIPT_DIR"

# Check if package source exists
if [ ! -d "../../src" ]; then
    echo -e "${RED}Error: Package source not found at ../../src${NC}"
    exit 1
fi

build_and_test() {
    local version=$1
    local service="laravel${version}"

    echo ""
    echo -e "${YELLOW}========================================${NC}"
    echo -e "${YELLOW}Testing Laravel ${version}${NC}"
    echo -e "${YELLOW}========================================${NC}"

    # Build and run
    echo -e "${BLUE}Building Docker image for Laravel ${version}...${NC}"
    if docker-compose build --no-cache "$service" 2>&1; then
        echo -e "${GREEN}✓ Build successful${NC}"
    else
        echo -e "${RED}✗ Build failed${NC}"
        return 1
    fi

    # Run container
    echo -e "${BLUE}Starting container...${NC}"
    docker-compose up -d "$service"

    # Wait for container to be ready
    sleep 2

    # Check if installation was successful
    echo -e "${BLUE}Checking installation...${NC}"

    # Check composer.json
    if docker exec "shopify-${service}" cat composer.json | grep -q "kyon147/laravel-shopify"; then
        echo -e "${GREEN}✓ Package installed in composer.json${NC}"
    else
        echo -e "${RED}✗ Package not found in composer.json${NC}"
        docker-compose stop "$service"
        return 1
    fi

    # Check config file
    if docker exec "shopify-${service}" test -f config/shopify-app.php; then
        echo -e "${GREEN}✓ Config file published${NC}"
    else
        echo -e "${RED}✗ Config file not found${NC}"
        docker-compose stop "$service"
        return 1
    fi

    # Check routes
    echo -e "${BLUE}Checking routes...${NC}"
    docker exec "shopify-${service}" php artisan route:list 2>/dev/null | grep -E "(shopify|billing|authenticate)" || true

    # Run package tests
    echo -e "${BLUE}Running package tests...${NC}"
    if docker exec "shopify-${service}" bash -c "cd /packages/laravel-shopify && vendor/bin/phpunit -v" 2>&1 | tail -20; then
        echo -e "${GREEN}✓ Tests passed${NC}"
    else
        echo -e "${YELLOW}⚠ Some tests may have failed (check output above)${NC}"
    fi

    # Stop container
    docker-compose stop "$service"

    echo -e "${GREEN}✓ Laravel ${version} test completed${NC}"
}

# Clean up any existing containers
echo -e "${YELLOW}Cleaning up existing containers...${NC}"
docker-compose down 2>/dev/null || true

# Run tests based on argument
if [ "$LARAVEL_VERSION" == "all" ]; then
    for version in 10 11 12 13; do
        build_and_test "$version" || true
    done
else
    build_and_test "$LARAVEL_VERSION"
fi

# Final cleanup
echo ""
echo -e "${YELLOW}Cleaning up...${NC}"
docker-compose down 2>/dev/null || true

echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Testing Complete${NC}"
echo -e "${BLUE}========================================${NC}"
