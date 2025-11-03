#!/bin/bash
# Cloudways Deployment Script
# Add this to Application Settings → Deployment via Git → Deployment Script Path

echo "🚀 Starting deployment..."

# Backup .env file before git operations
if [ -f .env ]; then
    echo "💾 Backing up .env file..."
    cp .env .env.backup
fi

# If .env doesn't exist, create from example
if [ ! -f .env ]; then
    echo "📝 Creating .env from .env.example..."
    cp .env.example .env
    echo "⚠️  WARNING: You need to configure .env with production settings!"
fi
# Cloudways Deployment Script
# Add this to Application Settings → Deployment via Git → Deployment Script Path

echo "🚀 Starting deployment..."

# Backup .env file before git operations
if [ -f .env ]; then
    echo "💾 Backing up .env file..."
    cp .env .env.backup
fi

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

# Restore .env if it was overwritten
if [ -f .env.backup ] && [ ! -s .env ]; then
    echo "🔄 Restoring .env file..."
    cp .env.backup .env
fi

echo "✅ Deployment complete!"
