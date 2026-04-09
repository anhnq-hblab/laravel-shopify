#!/bin/bash

# Docker testing script for Laravel Shopify package
# Usage: ./run-tests.sh [php_version] [laravel_version]
# Examples:
#   ./run-tests.sh           # Run all tests
#   ./run-tests.sh 8.3       # Run tests for PHP 8.3
#   ./run-tests.sh 8.3 11.0  # Run tests for PHP 8.3 + Laravel 11.0

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Laravel Shopify Package - Docker Testing${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

PHP_VERSION="${1:-all}"
LARAVEL_VERSION="${2:-all}"

# Build base images
echo -e "${YELLOW}Building Docker images...${NC}"
cd "$PROJECT_ROOT"

build_image() {
    local php_ver=$1
    echo -e "${BLUE}Building PHP ${php_ver} image...${NC}"
    docker build -f docker-testing/Dockerfile.php${php_ver//./} -t laravel-shopify-php${php_ver//./} . 2>&1 | grep -E '(Successfully|Error|failed)' || true
}

# Function to run tests for a specific combination
run_test() {
    local php_ver=$1
    local laravel_ver=$2
    local service_name="php${php_ver//./}-laravel${laravel_ver//./}"

    echo ""
    echo -e "${YELLOW}Testing PHP ${php_ver} + Laravel ${laravel_ver}...${NC}"

    # Check if we should skip this combination (from CI matrix)
    if [[ "$php_ver" == "8.1" && "$laravel_ver" == "13.0" ]]; then
        echo -e "${YELLOW}SKIP: PHP 8.1 + Laravel 13.0 (Laravel 13 requires PHP 8.2+)${NC}"
        return 0
    fi

    if [[ "$php_ver" == "8.4" && ("$laravel_ver" == "8.22" || "$laravel_ver" == "9.0" || "$laravel_ver" == "12.0") ]]; then
        echo -e "${YELLOW}SKIP: PHP 8.4 + Laravel ${laravel_ver} (from CI exclude matrix)${NC}"
        return 0
    fi

    if [[ "$php_ver" == "8.1" && ("$laravel_ver" == "8.22" || "$laravel_ver" == "9.0") ]]; then
        echo -e "${YELLOW}SKIP: PHP 8.1 + Laravel ${laravel_ver} (from CI exclude matrix)${NC}"
        return 0
    fi

    # Run the test using docker-compose
    cd "$PROJECT_ROOT/docker-testing"

    if docker-compose ps | grep -q "$service_name"; then
        echo "Stopping existing container..."
        docker-compose stop "$service_name" 2>/dev/null || true
        docker-compose rm -f "$service_name" 2>/dev/null || true
    fi

    echo "Running tests..."
    if docker-compose up --abort-on-container-exit "$service_name" 2>&1; then
        echo -e "${GREEN}✓ PASS: PHP ${php_ver} + Laravel ${laravel_ver}${NC}"
    else
        echo -e "${RED}✗ FAIL: PHP ${php_ver} + Laravel ${laravel_ver}${NC}"
        return 1
    fi
}

# Define test matrix (from CI matrix)
PHP_VERSIONS=("8.1" "8.2" "8.3" "8.4")
LARAVEL_VERSIONS=("10.0" "11.0" "12.0" "13.0")

# Run tests based on arguments
if [[ "$PHP_VERSION" == "all" && "$LARAVEL_VERSION" == "all" ]]; then
    echo -e "${YELLOW}Running full test matrix...${NC}"

    for php in "${PHP_VERSIONS[@]}"; do
        build_image "$php"
    done

    for php in "${PHP_VERSIONS[@]}"; do
        for laravel in "${LARAVEL_VERSIONS[@]}"; do
            run_test "$php" "$laravel" || true
        done
    done

elif [[ "$LARAVEL_VERSION" == "all" ]]; then
    echo -e "${YELLOW}Running tests for PHP ${PHP_VERSION}...${NC}"
    build_image "$PHP_VERSION"

    for laravel in "${LARAVEL_VERSIONS[@]}"; do
        run_test "$PHP_VERSION" "$laravel" || true
    done
else
    echo -e "${YELLOW}Running tests for PHP ${PHP_VERSION} + Laravel ${LARAVEL_VERSION}...${NC}"
    build_image "$PHP_VERSION"
    run_test "$PHP_VERSION" "$LARAVEL_VERSION"
fi

echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Testing Complete${NC}"
echo -e "${BLUE}========================================${NC}"
