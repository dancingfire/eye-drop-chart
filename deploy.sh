#!/bin/bash
# Minimal Cloudways Deployment Script

echo "Starting deployment..."

# Only run migrations and clear cache
 --force
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo "Deployment complete!"
