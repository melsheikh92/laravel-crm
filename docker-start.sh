#!/bin/bash

# ProvenSuccess CRM - Docker Quick Start Script

echo "🚀 Starting ProvenSuccess CRM with Docker..."
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker first."
    exit 1
fi

# Check if .env file exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        echo "⚠️  .env.example not found. Creating basic .env file..."
        cat > .env << EOF
APP_NAME="ProvenSuccess"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=provensuccess
DB_USERNAME=provensuccess
DB_PASSWORD=provensuccess
EOF
    fi
fi

# Start Docker containers
echo "🐳 Starting Docker containers..."
docker-compose -f docker-compose.simple.yml up -d --build

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 10

# Check if containers are running
if ! docker ps | grep -q provensuccess_app; then
    echo "❌ Failed to start containers. Check logs with: docker-compose logs"
    exit 1
fi

echo "✅ Containers started successfully!"
echo ""
echo "📦 Installing dependencies..."

# Install PHP dependencies
docker exec -it provensuccess_app composer install --no-interaction

# Generate application key if not set
echo "🔑 Generating application key..."
docker exec -it provensuccess_app php artisan key:generate --force

# Set permissions
echo "🔐 Setting permissions..."
docker exec -it provensuccess_app chmod -R 775 storage bootstrap/cache
docker exec -it provensuccess_app chown -R www-data:www-data storage bootstrap/cache

echo ""
echo "✅ Setup complete!"
echo ""
echo "📋 Next steps:"
echo "   1. Run the installer: docker exec -it provensuccess_app php artisan provensuccess-crm:install"
echo "   2. Access the application: http://localhost:8000"
echo "   3. Access admin panel: http://localhost:8000/admin/login"
echo ""
echo "📚 For more information, see DOCKER_SETUP.md"

