#!/bin/bash#!/bin/bash

# Cloudways Deployment Script# Minimal Cloudways Deployment Script



echo "🚀 Starting deployment..."echo "Starting deployment..."



# Run migrations# Only run migrations and clear cache

echo "🗄️  Running database migrations..." --force

php artisan migrate --forcephp artisan config:clear

php artisan cache:clear

# Clear all cachesphp artisan view:clear

echo "🧹 Clearing caches..."

php artisan config:clearecho "Deployment complete!"

php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
echo "⚙️  Optimizing..."
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
