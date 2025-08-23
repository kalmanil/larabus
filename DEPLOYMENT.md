# 🚀 Larabus Deployment Guide

This guide explains how to deploy Larabus framework to various hosting environments.

## 📦 **Pre-deployment Checklist**

- ✅ PHP 8.2+ on hosting
- ✅ Composer available 
- ✅ Git access (optional but recommended)
- ✅ Domain DNS configured
- ✅ SSL certificates ready

## 🌐 **Shared Hosting Deployment**

### Step 1: Upload Larabus

```bash
# Option A: Git clone (if Git available)
git clone https://github.com/Kalmanis/Larabus.git
cd Larabus
composer install --no-dev --optimize-autoloader

# Option B: Download and upload via FTP
# Download ZIP from GitHub, extract, upload to hosting
```

### Step 2: Directory Structure

```
your-hosting-account/
├── 📁 Larabus/                    # Outside public_html (secure)
│   ├── apps/
│   ├── vendor/
│   └── [framework files]
└── 📁 public_html/
    ├── 📁 domain1.com/            # Each domain gets folder
    │   ├── config.php
    │   ├── index.php → ../Larabus/
    │   └── .htaccess
    └── 📁 domain2.com/
        ├── config.php  
        ├── index.php → ../Larabus/
        └── .htaccess
```

### Step 3: Create Domain Folders

```bash
# Using the built-in script
php Larabus/create-domain.php mydomain.com myapp "My Website" "#3b82f6"

# Or manually create:
mkdir public_html/mydomain.com
```

### Step 4: Configure cPanel/WHM

1. **Add Domain**: Point `mydomain.com` to `public_html/mydomain.com/`
2. **Set Permissions**: 755 for folders, 644 for files
3. **SSL**: Install SSL certificate for each domain

## ☁️ **VPS/Cloud Server Deployment**

### Step 1: Server Setup

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install nginx php8.2-fpm php8.2-mbstring php8.2-xml php8.2-mysql composer git

# CentOS/RHEL  
sudo yum install nginx php82-fpm php82-mbstring php82-xml php82-mysql composer git
```

### Step 2: Deploy Larabus

```bash
cd /var/www
sudo git clone https://github.com/Kalmanis/Larabus.git
cd Larabus
sudo composer install --no-dev --optimize-autoloader
sudo php install.php
```

### Step 3: Nginx Configuration

```nginx
# /etc/nginx/sites-available/domain1.com
server {
    listen 80;
    listen [::]:80;
    server_name domain1.com www.domain1.com;
    root /var/www/domains/domain1.com;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### Step 4: Create Domain Structure

```bash
sudo mkdir -p /var/www/domains/domain1.com
sudo php /var/www/Larabus/create-domain.php domain1.com myapp "My Site"
sudo mv ../domain1.com/* /var/www/domains/domain1.com/
sudo chown -R www-data:www-data /var/www/domains/domain1.com
```

## 🐳 **Docker Deployment**

### Dockerfile

```dockerfile
FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    nginx \
    composer \
    && docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/larabus

COPY . .
RUN composer install --no-dev --optimize-autoloader

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
```

### docker-compose.yml

```yaml
version: '3.8'
services:
  larabus:
    build: .
    ports:
      - "80:80"
    volumes:
      - ./domains:/var/www/domains
    environment:
      - PHP_FPM_POOL=www
```

## 🔧 **Environment Configuration**

### Production .env Settings

```env
APP_NAME=Larabus
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=daily
LOG_LEVEL=error

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=larabus_production
DB_USERNAME=your_db_user
DB_PASSWORD=strong_password
```

## 🔒 **Security Configuration**

### 1. File Permissions

```bash
# Set correct permissions
sudo chown -R www-data:www-data /var/www/Larabus
sudo chmod -R 755 /var/www/Larabus/storage
sudo chmod -R 755 /var/www/Larabus/bootstrap/cache
sudo chmod 644 /var/www/Larabus/.env
```

### 2. Hide Sensitive Files

```nginx
# In Nginx config
location ~ /\.(env|git) {
    deny all;
}

location /vendor {
    deny all;
}
```

### 3. SSL Configuration

```nginx
# Force HTTPS redirect
server {
    listen 80;
    server_name domain1.com www.domain1.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name domain1.com www.domain1.com;
    
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/private.key;
    
    # ... rest of config
}
```

## 📊 **Performance Optimization**

### 1. PHP Optimization

```ini
# php.ini optimizations
memory_limit = 256M
max_execution_time = 300
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 4000
```

### 2. Laravel Optimization

```bash
# Production optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 3. Database Optimization

```sql
-- Index frequently queried columns
CREATE INDEX idx_domain_app ON domains(app_name);
CREATE INDEX idx_created_at ON logs(created_at);
```

## 🎯 **Multi-Domain Setup Example**

### Complete Setup for 3 Domains

```bash
# Deploy Larabus
git clone https://github.com/Kalmanis/Larabus.git
cd Larabus
composer install --no-dev

# Create domains
php create-domain.php shop.com ecommerce "My Shop" "#e11d48"
php create-domain.php blog.com blog "My Blog" "#059669" 
php create-domain.php portfolio.com portfolio "My Portfolio" "#7c3aed"

# Move to web directories
mv ../shop.com /var/www/domains/
mv ../blog.com /var/www/domains/
mv ../portfolio.com /var/www/domains/

# Configure Nginx virtual hosts for each domain
# Point DNS A records to server IP
# Install SSL certificates
```

## 🚨 **Troubleshooting**

### Common Issues

1. **500 Internal Server Error**
   - Check storage permissions: `chmod -R 755 storage/`
   - Verify .env file exists and APP_KEY is set
   - Check error logs: `tail -f storage/logs/laravel.log`

2. **Routes Not Working**
   - Ensure .htaccess is uploaded and mod_rewrite enabled
   - Check domain config.php syntax
   - Verify index.php path to Larabus

3. **Database Connection Failed**
   - Verify database credentials in .env
   - Check if database exists
   - Test connection: `php artisan tinker` then `DB::connection()->getPdo()`

### Performance Issues

1. **Slow Loading**
   - Enable PHP OPcache
   - Cache Laravel configs: `php artisan optimize`
   - Use CDN for static assets
   - Optimize database queries

2. **Memory Issues**
   - Increase PHP memory_limit
   - Optimize Composer autoloader: `composer dump-autoload --optimize`
   - Use database session driver instead of file

## 📞 **Support**

- 📖 **Documentation**: [GitHub Wiki](https://github.com/Kalmanis/Larabus/wiki)
- 🐛 **Bug Reports**: [GitHub Issues](https://github.com/Kalmanis/Larabus/issues)
- 💬 **Community**: [Discussions](https://github.com/Kalmanis/Larabus/discussions)

---

🚍 **Happy hosting with Larabus!**
