#!/bin/bash

echo "🚀 Starting Laravel with Ngrok support..."
echo ""

# Build assets first for ngrok
echo "📦 Building assets..."
bun run build

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear  
php artisan view:clear

# Start server
echo "🌐 Starting Laravel server..."
echo "   Use ngrok: ngrok http 8000 --host-header=rewrite"
echo ""
php artisan serve
