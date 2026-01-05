# Docker Setup Guide
## ProvenSuccess CRM - Local Development with Docker

This guide will help you set up ProvenSuccess CRM locally using Docker Compose with MySQL database.

---

## 📋 Prerequisites

- **Docker** (version 20.10 or higher)
- **Docker Compose** (version 2.0 or higher)
- **Git** (to clone the repository)

### Check Docker Installation

```bash
docker --version
docker-compose --version
```

---

## 🚀 Quick Start

### Option 1: Simple Setup (Recommended for Development)

This setup includes MySQL and Laravel app only.

```bash
# Use the simple docker-compose file
docker-compose -f docker-compose.simple.yml up -d --build
```

### Option 2: Full Setup (with Nginx)

This setup includes MySQL, Laravel app, and Nginx web server.

```bash
# Use the full docker-compose file
docker-compose up -d --build
```

---

## 📝 Step-by-Step Setup

### Step 1: Clone/Prepare the Project

If you haven't already:

```bash
cd /path/to/laravel-crm
```

### Step 2: Create Environment File

Copy the example environment file:

```bash
cp .env.example .env
```

### Step 3: Update .env File for Docker

Edit `.env` file and update these database settings:

```env
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
```

**Note**: The `DB_HOST=mysql` matches the MySQL service name in docker-compose.yml

### Step 4: Start Docker Containers

```bash
# For simple setup (recommended)
docker-compose -f docker-compose.simple.yml up -d --build

# OR for full setup with Nginx
docker-compose up -d --build
```

This will:
- Build the PHP/Laravel container
- Start MySQL container
- Create network and volumes
- Start all services

### Step 5: Install Dependencies

```bash
# Enter the app container
docker exec -it provensuccess_app bash

# Install PHP dependencies
composer install

# Install Node dependencies (if needed)
npm install

# Exit container
exit
```

### Step 6: Generate Application Key

```bash
docker exec -it provensuccess_app php artisan key:generate
```

### Step 7: Run Database Migrations & Install

```bash
# Run the ProvenSuccess installer
docker exec -it provensuccess_app php artisan provensuccess-crm:install

# OR manually:
# docker exec -it provensuccess_app php artisan migrate
# docker exec -it provensuccess_app php artisan db:seed
```

### Step 8: Set Permissions

```bash
docker exec -it provensuccess_app chmod -R 775 storage bootstrap/cache
docker exec -it provensuccess_app chown -R www-data:www-data storage bootstrap/cache
```

### Step 9: Access the Application

- **Application**: http://localhost:8000
- **Admin Panel**: http://localhost:8000/admin/login
- **Installer**: http://localhost:8000/installer

**Default Admin Credentials** (if created during installation):
- Email: `admin@example.com`
- Password: `admin123`

---

## 🛠️ Useful Docker Commands

### View Running Containers

```bash
docker ps
```

### View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f mysql
```

### Execute Commands in Container

```bash
# Enter app container shell
docker exec -it provensuccess_app bash

# Run artisan commands
docker exec -it provensuccess_app php artisan migrate
docker exec -it provensuccess_app php artisan cache:clear

# Run composer commands
docker exec -it provensuccess_app composer install
docker exec -it provensuccess_app composer update

# Run npm commands
docker exec -it provensuccess_app npm install
docker exec -it provensuccess_app npm run build
```

### Stop Containers

```bash
# Stop all containers
docker-compose down

# Stop and remove volumes (WARNING: deletes database data)
docker-compose down -v
```

### Restart Containers

```bash
docker-compose restart
```

### Rebuild Containers

```bash
# Rebuild after Dockerfile changes
docker-compose up -d --build
```

---

## 🗄️ Database Access

### Connect from Host Machine

You can connect to MySQL from your host machine using:

- **Host**: `localhost` or `127.0.0.1`
- **Port**: `3306`
- **Database**: `provensuccess`
- **Username**: `provensuccess`
- **Password**: `provensuccess`
- **Root Password**: `root`

### Using MySQL Client

```bash
# From host machine
mysql -h 127.0.0.1 -P 3306 -u provensuccess -pprovensuccess provensuccess

# Or using root
mysql -h 127.0.0.1 -P 3306 -u root -proot
```

### Using Database GUI Tools

Use tools like:
- **MySQL Workbench**
- **phpMyAdmin** (add to docker-compose if needed)
- **TablePlus**
- **DBeaver**

Connection details:
- Host: `localhost`
- Port: `3306`
- Username: `provensuccess`
- Password: `provensuccess`

---

## 🔧 Troubleshooting

### Port Already in Use

If port 8000 or 3306 is already in use:

1. **Change ports in docker-compose.yml**:
   ```yaml
   ports:
     - "8001:8000"  # Change 8001 to any available port
   ```

2. **Update APP_URL in .env**:
   ```env
   APP_URL=http://localhost:8001
   ```

### Permission Denied Errors

```bash
# Fix storage permissions
docker exec -it provensuccess_app chmod -R 775 storage bootstrap/cache
docker exec -it provensuccess_app chown -R www-data:www-data storage bootstrap/cache
```

### Database Connection Errors

1. **Check MySQL is running**:
   ```bash
   docker ps | grep mysql
   ```

2. **Check MySQL logs**:
   ```bash
   docker-compose logs mysql
   ```

3. **Verify .env database settings**:
   ```env
   DB_HOST=mysql  # Must match service name in docker-compose.yml
   DB_PORT=3306
   DB_DATABASE=provensuccess
   DB_USERNAME=provensuccess
   DB_PASSWORD=provensuccess
   ```

### Container Won't Start

1. **Check logs**:
   ```bash
   docker-compose logs app
   ```

2. **Rebuild containers**:
   ```bash
   docker-compose down
   docker-compose up -d --build
   ```

### Clear Everything and Start Fresh

```bash
# Stop and remove everything
docker-compose down -v

# Remove images (optional)
docker rmi provensuccess_app

# Start fresh
docker-compose up -d --build
```

---

## 📦 Adding phpMyAdmin (Optional)

To add phpMyAdmin for database management, add this to `docker-compose.yml`:

```yaml
  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: provensuccess_phpmyadmin
    restart: unless-stopped
    ports:
      - "8080:80"
    environment:
      PMA_HOST: mysql
      PMA_PORT: 3306
      MYSQL_ROOT_PASSWORD: root
    networks:
      - provensuccess_network
    depends_on:
      - mysql
```

Then access phpMyAdmin at: http://localhost:8080

---

## 🎯 Development Workflow

### Daily Development

1. **Start containers**:
   ```bash
   docker-compose up -d
   ```

2. **Make code changes** (files are synced via volumes)

3. **Run migrations**:
   ```bash
   docker exec -it provensuccess_app php artisan migrate
   ```

4. **Clear cache**:
   ```bash
   docker exec -it provensuccess_app php artisan cache:clear
   ```

5. **Stop containers** (when done):
   ```bash
   docker-compose down
   ```

### Building Frontend Assets

```bash
# Install dependencies
docker exec -it provensuccess_app npm install

# Build assets
docker exec -it provensuccess_app npm run build

# Or watch for changes
docker exec -it provensuccess_app npm run dev
```

---

## 📊 Container Information

### Services Overview

- **mysql**: MySQL 8.0 database
- **app**: PHP 8.2-FPM with Laravel application
- **nginx**: (Optional) Nginx web server

### Volumes

- **mysql_data**: Persistent MySQL data storage
- **Project files**: Synced from host to container
- **storage**: Laravel storage directory
- **bootstrap/cache**: Laravel cache directory

### Networks

- **provensuccess_network**: Bridge network connecting all services

---

## 🔐 Security Notes

⚠️ **Important**: This setup is for **local development only**. Do NOT use these settings in production!

For production:
- Use strong passwords
- Don't expose MySQL port publicly
- Use environment-specific configurations
- Enable SSL/TLS
- Use proper firewall rules

---

## 📚 Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Laravel Documentation](https://laravel.com/docs)

---

## ✅ Quick Checklist

- [ ] Docker and Docker Compose installed
- [ ] `.env` file created and configured
- [ ] Containers started successfully
- [ ] Dependencies installed
- [ ] Application key generated
- [ ] Database migrations run
- [ ] Application accessible at http://localhost:8000

---

**Happy Coding! 🚀**

