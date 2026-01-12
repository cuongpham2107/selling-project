#!/bin/bash

# Script to quickly configure Laravel for ngrok usage

if [ -z "$1" ]; then
    echo "❌ Please provide ngrok URL"
    echo "Usage: ./setup-ngrok.sh <ngrok-url>"
    echo "Example: ./setup-ngrok.sh https://abc123.ngrok-free.dev"
    exit 1
fi

NGROK_URL=$1
NGROK_DOMAIN=$(echo $NGROK_URL | sed 's/https\?:\/\///')

echo "🔧 Configuring Laravel for ngrok..."
echo "   URL: $NGROK_URL"
echo "   Domain: $NGROK_DOMAIN"

# Update .env file
if [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS
    sed -i '' "s|^APP_URL=.*|APP_URL=$NGROK_URL|g" .env
    sed -i '' "s|^ASSET_URL=.*|ASSET_URL=$NGROK_URL|g" .env
    sed -i '' "s|^SESSION_DOMAIN=.*|SESSION_DOMAIN=.ngrok-free.dev|g" .env
else
    # Linux
    sed -i "s|^APP_URL=.*|APP_URL=$NGROK_URL|g" .env
    sed -i "s|^ASSET_URL=.*|ASSET_URL=$NGROK_URL|g" .env
    sed -i "s|^SESSION_DOMAIN=.*|SESSION_DOMAIN=.ngrok-free.dev|g" .env
fi

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear

echo "✅ Done! Your app is configured for ngrok"
echo ""
echo "📱 Now you can access from mobile: $NGROK_URL"
echo ""
echo "💡 Remember to restart your Laravel server if it's running"
