#!/bin/bash

# Development setup script for Flex Videos plugin
# This script helps set up the development environment and run common tasks

set -e

echo "🚀 Flex Videos Development Setup"
echo "================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "flex-videos.php" ]; then
    print_error "This script must be run from the plugin root directory"
    exit 1
fi

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check dependencies
echo "Checking dependencies..."

if ! command_exists node; then
    print_error "Node.js is required but not installed"
    exit 1
fi
print_status "Node.js is installed"

if ! command_exists npm; then
    print_error "npm is required but not installed"
    exit 1
fi
print_status "npm is installed"

if ! command_exists php; then
    print_error "PHP is required but not installed"
    exit 1
fi
print_status "PHP is installed"

if ! command_exists composer; then
    print_error "Composer is required but not installed"
    echo "Install Composer from https://getcomposer.org/"
    exit 1
fi
print_status "Composer is installed"

# Install dependencies
echo ""
echo "Installing dependencies..."

echo "Installing npm packages..."
npm install
print_status "npm packages installed"

echo "Installing Composer packages..."
composer install --no-interaction
print_status "Composer packages installed"

# Build assets
echo ""
echo "Building assets..."
npm run build
print_status "Assets built successfully"

# Run linting
echo ""
echo "Running code quality checks..."

if [ -f "vendor/bin/phpcs" ]; then
    echo "Checking PHP coding standards..."
    if vendor/bin/phpcs --standard=WordPress --ignore=vendor/,node_modules/,tests/ . --extensions=php; then
        print_status "PHP coding standards check passed"
    else
        print_warning "PHP coding standards issues found. Run 'composer run phpcbf' to auto-fix"
    fi
else
    print_warning "PHPCS not available, skipping coding standards check"
fi

# Validate plugin structure
echo ""
echo "Validating plugin structure..."

required_files=(
    "flex-videos.php"
    "README.md"
    "composer.json"
    "package.json"
    "assets/css/flex-videos.css"
    "assets/js/flex-videos-flyout.js"
)

for file in "${required_files[@]}"; do
    if [ -f "$file" ]; then
        print_status "$file exists"
    else
        print_error "$file is missing"
    fi
done

# Security checks
echo ""
echo "Running security checks..."

echo "Checking for dangerous functions..."
if grep -r "eval\|exec\|system\|shell_exec\|passthru" --include="*.php" --exclude-dir=vendor --exclude-dir=node_modules .; then
    print_warning "Potentially dangerous functions found in code"
else
    print_status "No dangerous functions found"
fi

echo "Checking nonce usage..."
if grep -q "wp_nonce_field\|check_admin_referer" flex-videos.php; then
    print_status "Nonce usage found in main file"
else
    print_error "No nonce usage found - security issue"
fi

# Plugin validation
echo ""
echo "Validating plugin syntax..."
if php -l flex-videos.php > /dev/null; then
    print_status "Plugin PHP syntax is valid"
else
    print_error "Plugin PHP syntax errors found"
    exit 1
fi

# Final summary
echo ""
echo "🎉 Development environment setup complete!"
echo ""
echo "Available commands:"
echo "  npm run build     - Build block assets"
echo "  npm run start     - Start development server"
echo "  composer test     - Run PHPUnit tests"
echo "  composer phpcs    - Check coding standards"
echo "  composer phpcbf   - Fix coding standards"
echo ""
echo "Documentation:"
echo "  README.md         - User documentation"
echo "  DEVELOPER.md      - Developer documentation"
echo "  CONTRIBUTING.md   - Contribution guidelines"
echo ""

print_status "Ready for development! 🚀"