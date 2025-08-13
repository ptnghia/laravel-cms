# Laravel CMS Deployment Guide

## Overview

This guide covers deploying Laravel CMS to production environments with best practices for security, performance, and scalability.

## Prerequisites

### System Requirements

- **PHP**: 8.2 or higher
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Nginx or Apache
- **Cache**: Redis (recommended) or Memcached
- **Queue**: Redis, Database, or SQS
- **Storage**: Local or S3-compatible storage

### Server Specifications

#### Minimum Requirements
- **CPU**: 1 vCPU
- **RAM**: 2GB
- **Storage**: 20GB SSD
- **Bandwidth**: 100 Mbps

#### Recommended for Production
- **CPU**: 2+ vCPUs
- **RAM**: 4GB+
- **Storage**: 50GB+ SSD
- **Bandwidth**: 1 Gbps

## Environment Setup

### 1. Server Preparation

#### Ubuntu/Debian
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx mysql-server redis-server php8.3-fpm \
    php8.3-mysql php8.3-redis php8.3-xml php8.3-mbstring \
    php8.3-curl php8.3-zip php8.3-gd php8.3-intl \
    composer git unzip supervisor

# Install Node.js (for asset compilation)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

#### CentOS/RHEL
```bash
# Update system
sudo yum update -y

# Install EPEL and Remi repositories
sudo yum install -y epel-release
sudo yum install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm

# Install required packages
sudo yum install -y nginx mysql-server redis php83-php-fpm \
    php83-php-mysql php83-php-redis php83-php-xml \
    php83-php-mbstring php83-php-curl php83-php-zip \
    php83-php-gd php83-php-intl composer git unzip supervisor
```

### 2. Database Setup

#### MySQL Configuration
```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
CREATE DATABASE laravel_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'laravel_cms'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON laravel_cms.* TO 'laravel_cms'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### PostgreSQL Configuration
```bash
# Install PostgreSQL
sudo apt install -y postgresql postgresql-contrib

# Create database and user
sudo -u postgres psql
```

```sql
CREATE DATABASE laravel_cms;
CREATE USER laravel_cms WITH PASSWORD 'secure_password';
GRANT ALL PRIVILEGES ON DATABASE laravel_cms TO laravel_cms;
\q
```

### 3. Redis Configuration

```bash
# Configure Redis
sudo nano /etc/redis/redis.conf
```

```conf
# Security
requirepass your_redis_password
bind 127.0.0.1

# Performance
maxmemory 256mb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000
```

```bash
# Restart Redis
sudo systemctl restart redis-server
sudo systemctl enable redis-server
```

## Application Deployment

### 1. Clone Repository

```bash
# Create application directory
sudo mkdir -p /var/www/laravel-cms
sudo chown $USER:$USER /var/www/laravel-cms

# Clone repository
cd /var/www/laravel-cms
git clone https://github.com/your-username/laravel-cms.git .

# Set permissions
sudo chown -R www-data:www-data /var/www/laravel-cms
sudo chmod -R 755 /var/www/laravel-cms
sudo chmod -R 775 /var/www/laravel-cms/storage
sudo chmod -R 775 /var/www/laravel-cms/bootstrap/cache
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
npm ci --production

# Build assets
npm run build
```

### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure environment
nano .env
```

```env
# Application
APP_NAME="Laravel CMS"
APP_ENV=production
APP_KEY=base64:generated_key_here
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_cms
DB_USERNAME=laravel_cms
DB_PASSWORD=secure_password

# Cache
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# Session
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Mail
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls

# File Storage
FILESYSTEM_DISK=local
# For S3: FILESYSTEM_DISK=s3
# AWS_ACCESS_KEY_ID=your_key
# AWS_SECRET_ACCESS_KEY=your_secret
# AWS_DEFAULT_REGION=us-east-1
# AWS_BUCKET=your-bucket

# Security
SANCTUM_STATEFUL_DOMAINS=your-domain.com
```

### 4. Database Migration

```bash
# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --class=RolePermissionSeeder

# Create admin user
php artisan make:admin
```

### 5. Optimization

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize

# Create symbolic link for storage
php artisan storage:link

# Warm up caches
php artisan performance:monitor --cache
```

## Web Server Configuration

### Nginx Configuration

```bash
# Create site configuration
sudo nano /etc/nginx/sites-available/laravel-cms
```

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/laravel-cms/public;

    # SSL Configuration
    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    ssl_prefer_server_ciphers off;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    # File Upload Limits
    client_max_body_size 100M;

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/laravel-cms /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Apache Configuration

```bash
# Create virtual host
sudo nano /etc/apache2/sites-available/laravel-cms.conf
```

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    Redirect permanent / https://your-domain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName your-domain.com
    DocumentRoot /var/www/laravel-cms/public

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/ssl/certificate.crt
    SSLCertificateKeyFile /path/to/ssl/private.key

    # Security Headers
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-Content-Type-Options nosniff
    Header always set X-XSS-Protection "1; mode=block"

    # Compression
    LoadModule deflate_module modules/mod_deflate.so
    <Location />
        SetOutputFilter DEFLATE
    </Location>

    <Directory /var/www/laravel-cms/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/laravel-cms_error.log
    CustomLog ${APACHE_LOG_DIR}/laravel-cms_access.log combined
</VirtualHost>
```

```bash
# Enable modules and site
sudo a2enmod rewrite ssl headers deflate
sudo a2ensite laravel-cms
sudo systemctl restart apache2
```

## Queue Worker Setup

### Supervisor Configuration

```bash
# Create supervisor configuration
sudo nano /etc/supervisor/conf.d/laravel-cms-worker.conf
```

```ini
[program:laravel-cms-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/laravel-cms/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/laravel-cms/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Update supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-cms-worker:*
```

## SSL Certificate

### Let's Encrypt (Certbot)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal
sudo crontab -e
```

```cron
0 12 * * * /usr/bin/certbot renew --quiet
```

## Monitoring and Logging

### Log Rotation

```bash
# Create logrotate configuration
sudo nano /etc/logrotate.d/laravel-cms
```

```
/var/www/laravel-cms/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

### Health Checks

```bash
# Create health check script
nano /var/www/laravel-cms/health-check.sh
```

```bash
#!/bin/bash
curl -f http://localhost/api/health || exit 1
```

## Backup Strategy

### Database Backup

```bash
# Create backup script
nano /var/www/laravel-cms/backup.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/laravel-cms"
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u laravel_cms -p laravel_cms > $BACKUP_DIR/database_$DATE.sql

# File backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/laravel-cms/storage/app/public

# Clean old backups (keep 30 days)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

```bash
# Schedule backup
sudo crontab -e
```

```cron
0 2 * * * /var/www/laravel-cms/backup.sh
```

## Security Checklist

- [ ] SSL certificate installed and configured
- [ ] Firewall configured (UFW/iptables)
- [ ] Database access restricted to localhost
- [ ] Redis password protected
- [ ] File permissions properly set
- [ ] Debug mode disabled in production
- [ ] Security headers configured
- [ ] Regular security updates scheduled
- [ ] Backup strategy implemented
- [ ] Monitoring and alerting configured

## Performance Optimization

### PHP-FPM Tuning

```bash
# Edit PHP-FPM configuration
sudo nano /etc/php/8.3/fpm/pool.d/www.conf
```

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

### OPcache Configuration

```bash
# Edit PHP configuration
sudo nano /etc/php/8.3/fpm/conf.d/10-opcache.ini
```

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

## Troubleshooting

### Common Issues

1. **Permission Errors**
   ```bash
   sudo chown -R www-data:www-data /var/www/laravel-cms
   sudo chmod -R 755 /var/www/laravel-cms
   sudo chmod -R 775 /var/www/laravel-cms/storage
   ```

2. **Queue Not Processing**
   ```bash
   sudo supervisorctl restart laravel-cms-worker:*
   php artisan queue:restart
   ```

3. **Cache Issues**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

### Log Locations

- **Application Logs**: `/var/www/laravel-cms/storage/logs/`
- **Nginx Logs**: `/var/log/nginx/`
- **PHP-FPM Logs**: `/var/log/php8.3-fpm.log`
- **MySQL Logs**: `/var/log/mysql/`

## Maintenance

### Regular Tasks

- **Daily**: Check logs and system health
- **Weekly**: Review performance metrics
- **Monthly**: Update dependencies and security patches
- **Quarterly**: Full system backup and disaster recovery test

### Update Process

```bash
# Backup before update
./backup.sh

# Pull latest changes
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo supervisorctl restart laravel-cms-worker:*
sudo systemctl reload php8.3-fpm
```

This deployment guide ensures a secure, performant, and maintainable Laravel CMS installation in production environments.
