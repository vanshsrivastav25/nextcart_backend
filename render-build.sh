#!/bin/bash
# backend/render-build.sh

echo "🚀 Starting NextCart Backend Deployment on Render..."

# Install dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Generate application key if not exists
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Setup storage
echo "🗄️  Setting up storage..."
php artisan storage:link

# Cache configuration
echo "⚙️  Caching configuration..."
php artisan config:cache

# Cache routes
echo "🛣️  Caching routes..."
php artisan route:cache

# Cache views
echo "👁️  Caching views..."
php artisan view:cache

# Run migrations
echo "📊 Running database migrations..."
php artisan migrate --force

# Clear and cache
echo "🧹 Clearing old cache..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Optimize
echo "⚡ Optimizing application..."
php artisan optimize

# Set permissions
echo "🔒 Setting permissions..."
chmod -R 775 storage bootstrap/cache

echo "✅ NextCart Backend deployment completed successfully!"
echo "🌐 Your app will be available at: https://nextcart-backend.onrender.com"