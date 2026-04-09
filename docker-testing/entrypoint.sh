#!/bin/sh
set -e

# Copy the correct composer.json for the Laravel version
if [ -f "composer.json.laravel${LARAVEL_VERSION}" ]; then
    echo "Using composer.json.laravel${LARAVEL_VERSION}"
    cp composer.json.laravel${LARAVEL_VERSION} composer.json
else
    echo "Warning: composer.json.laravel${LARAVEL_VERSION} not found, using default composer.json"
fi

# Remove lock file to ensure fresh install
rm -f composer.lock

# Install dependencies
echo "Installing dependencies..."
composer install --no-interaction --prefer-dist

# Run tests
echo "Running tests..."
vendor/bin/phpunit --testdox
