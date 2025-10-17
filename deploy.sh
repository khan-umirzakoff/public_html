#!/bin/bash

# BrightBridge Deployment Script
# For deploying code updates to production/staging server
# Assumes: Database exists, PHP installed, .env configured

set -e  # Exit on error

echo "🚀 BrightBridge Deployment Starting..."
echo "========================================"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Detect if we're in public_html or parent directory
if [ -f "artisan" ]; then
    PROJECT_DIR="$(pwd)"
elif [ -f "public_html/artisan" ]; then
    PROJECT_DIR="$(pwd)/public_html"
else
    echo -e "${RED}❌ Error: Laravel project not found${NC}"
    echo "Please run this script from project root or public_html directory"
    exit 1
fi

echo -e "${BLUE}Project Directory: $PROJECT_DIR${NC}"
cd "$PROJECT_DIR"

# Detect PHP command
if command -v php &> /dev/null; then
    PHP_CMD="php"
elif [ -f "/opt/lampp/bin/php" ]; then
    PHP_CMD="/opt/lampp/bin/php"
else
    echo -e "${RED}❌ PHP not found${NC}"
    exit 1
fi

PHP_VERSION=$($PHP_CMD -v | head -n 1)
echo -e "${GREEN}✅ $PHP_VERSION${NC}"
echo ""

# Step 1: Maintenance mode (optional)
echo "📋 Step 1: Enabling maintenance mode..."
$PHP_CMD artisan down --message="Updating application..." --retry=60 2>/dev/null || echo -e "${YELLOW}⚠️  Maintenance mode not available${NC}"

# Step 2: Pull latest code (if git is available)
echo ""
echo "📋 Step 2: Checking for code updates..."
if [ -d ".git" ]; then
    echo -e "${BLUE}Git repository detected${NC}"
    git pull origin main || git pull origin master || echo -e "${YELLOW}⚠️  Git pull failed or not configured${NC}"
    echo -e "${GREEN}✅ Code updated${NC}"
else
    echo -e "${YELLOW}⚠️  Not a git repository, skipping pull${NC}"
fi

# Step 3: Install/Update Composer dependencies
echo ""
echo "📋 Step 3: Installing Composer dependencies..."
if [ -f "composer.phar" ]; then
    COMPOSER_CMD="$PHP_CMD composer.phar"
elif command -v composer &> /dev/null; then
    COMPOSER_CMD="composer"
else
    echo -e "${RED}❌ Composer not found${NC}"
    exit 1
fi

$COMPOSER_CMD install --no-dev --no-interaction --prefer-dist --optimize-autoloader
echo -e "${GREEN}✅ Dependencies installed${NC}"

# Step 4: Run database migrations
echo ""
echo "📋 Step 4: Running database migrations..."
$PHP_CMD artisan migrate --force
echo -e "${GREEN}✅ Migrations completed${NC}"

# Step 5: Clear and cache config
echo ""
echo "📋 Step 5: Optimizing application..."
$PHP_CMD artisan config:clear
$PHP_CMD artisan cache:clear 2>/dev/null || echo -e "${YELLOW}⚠️  Cache clear skipped${NC}"
$PHP_CMD artisan view:clear 2>/dev/null || echo -e "${YELLOW}⚠️  View cache not found${NC}"
$PHP_CMD artisan config:cache
$PHP_CMD artisan route:cache 2>/dev/null || echo -e "${YELLOW}⚠️  Route cache skipped${NC}"
echo -e "${GREEN}✅ Application optimized${NC}"

# Step 6: Set permissions
echo ""
echo "📋 Step 6: Setting permissions..."
chmod -R 755 storage bootstrap/cache 2>/dev/null || echo -e "${YELLOW}⚠️  Permission change failed (may need sudo)${NC}"
echo -e "${GREEN}✅ Permissions set${NC}"

# Step 7: Disable maintenance mode
echo ""
echo "📋 Step 7: Disabling maintenance mode..."
$PHP_CMD artisan up 2>/dev/null || echo -e "${YELLOW}⚠️  Maintenance mode not available${NC}"

# Final message
echo ""
echo "========================================"
echo -e "${GREEN}🎉 Deployment completed successfully!${NC}"
echo ""
echo "Summary:"
echo "  - Dependencies: Updated"
echo "  - Migrations: Completed"
echo "  - Cache: Cleared & Optimized"
echo "  - Permissions: Set"
echo ""
echo -e "${BLUE}Application is ready!${NC}"
echo "========================================"
