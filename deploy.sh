#!/bin/bash
# Cloudways Deployment Script
# Add this to Application Settings → Deployment via Git → Deployment Script Path

echo "🚀 Starting deployment..."

# Install dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Run migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Clear and cache config
echo "⚙️  Optimizing configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ensure storage link exists
echo "🔗 Creating storage symlink..."
php artisan storage:link

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage bootstrap/cache

echo "✅ Deployment complete!"
