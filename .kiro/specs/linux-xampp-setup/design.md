# Design Document

## Overview

Bu design hujjati BrightBridge Laravel 5.8 ilovasini Linux muhitida XAMPP 7.4.33 yordamida sozlash uchun to'liq arxitektura va qadamlarni belgilaydi. Loyiha Windows/Laragon muhitidan Linux/XAMPP muhitiga ko'chirilmoqda, asosiy kod o'zgartirilmasdan.

### Maqsad
- Real serverdan olingan Laravel ilovasini local Linux muhitida ishga tushirish
- XAMPP'ning Apache va MySQL xizmatlaridan foydalanish
- Virtual host orqali `brightbridge.local` domenida ishlash
- Database import va konfiguratsiya
- Production kodiga tegmasdan faqat muhit sozlamalarini o'zgartirish

## Architecture

### System Components

```
┌─────────────────────────────────────────────────────────┐
│                    Linux OS (Ubuntu/Debian)              │
│  ┌───────────────────────────────────────────────────┐  │
│  │              XAMPP 7.4.33                         │  │
│  │  ┌─────────────────┐    ┌──────────────────┐    │  │
│  │  │  Apache 2.4     │    │   MySQL 5.7      │    │  │
│  │  │  (Port 80)      │    │   (Port 3306)    │    │  │
│  │  │                 │    │                  │    │  │
│  │  │  Virtual Host   │    │  brightbridge_   │    │  │
│  │  │  brightbridge   │◄───┤  local DB        │    │  │
│  │  │  .local         │    │                  │    │  │
│  │  └────────┬────────┘    └──────────────────┘    │  │
│  │           │                                      │  │
│  │           │ PHP 7.4                              │  │
│  │           │ (mod_php)                            │  │
│  │           ▼                                      │  │
│  │  ┌──────────────────────────────────────────┐  │  │
│  │  │    Laravel 5.8 Application               │  │  │
│  │  │    /opt/lampp/htdocs/BrightBridge/       │  │  │
│  │  │         public_html/                     │  │  │
│  │  └──────────────────────────────────────────┘  │  │
│  └───────────────────────────────────────────────────┘  │
│                                                          │
│  ┌───────────────────────────────────────────────────┐  │
│  │  /etc/hosts                                       │  │
│  │  127.0.0.1  brightbridge.local                    │  │
│  └───────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
```

### Directory Structure

```
/opt/lampp/                          # XAMPP installation root
├── bin/
│   ├── php                          # PHP 7.4 binary
│   └── mysql                        # MySQL client
├── etc/
│   ├── httpd.conf                   # Apache main config
│   └── extra/
│       └── httpd-vhosts.conf        # Virtual hosts config
├── htdocs/
│   └── BrightBridge/                # Loyiha joylashuvi
│       ├── public_html/             # Laravel root
│       │   ├── app/
│       │   ├── bootstrap/
│       │   ├── config/
│       │   ├── database/
│       │   ├── public/              # DocumentRoot
│       │   ├── resources/
│       │   ├── routes/
│       │   ├── storage/             # Writable
│       │   ├── vendor/              # Composer dependencies
│       │   ├── .env                 # Environment config
│       │   ├── artisan
│       │   └── composer.json
│       └── brightbr_job.sql         # Database dump
└── var/mysql/                       # MySQL data directory
```

## Components and Interfaces

### 1. XAMPP Configuration Component

**Purpose:** XAMPP muhitini Laravel uchun tayyorlash

**Key Files:**
- `/opt/lampp/etc/php.ini` - PHP konfiguratsiyasi
- `/opt/lampp/etc/httpd.conf` - Apache asosiy konfiguratsiya
- `/opt/lampp/etc/extra/httpd-vhosts.conf` - Virtual host sozlamalari

**Required PHP Extensions:**
```ini
extension=mbstring
extension=openssl
extension=pdo_mysql
extension=tokenizer
extension=xml
extension=ctype
extension=json
extension=fileinfo
extension=gd
```

**PHP Settings:**
```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 600
max_input_time = 600
memory_limit = 512M
display_errors = On          # Local development
error_reporting = E_ALL      # Local development
```

### 2. Apache Virtual Host Component

**Purpose:** `brightbridge.local` domenini Laravel public papkasiga yo'naltirish

**Configuration File:** `/opt/lampp/etc/extra/httpd-vhosts.conf`

**Virtual Host Template:**
```apache
<VirtualHost *:80>
    ServerName brightbridge.local
    ServerAlias www.brightbridge.local
    DocumentRoot "/opt/lampp/htdocs/BrightBridge/public_html/public"
    
    <Directory "/opt/lampp/htdocs/BrightBridge/public_html/public">
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
        
        # Laravel URL rewriting
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>
    
    # Logs
    ErrorLog "logs/brightbridge-error.log"
    CustomLog "logs/brightbridge-access.log" combined
    
    # PHP settings
    php_value upload_max_filesize 100M
    php_value post_max_size 100M
    php_value max_execution_time 600
    php_value memory_limit 512M
</VirtualHost>
```

**Required Apache Modules:**
- mod_rewrite (URL rewriting)
- mod_php (PHP processing)
- mod_dir (Directory indexing)

**Hosts File:** `/etc/hosts`
```
127.0.0.1    localhost
127.0.0.1    brightbridge.local
```

### 3. MySQL Database Component

**Purpose:** Laravel ilovasi uchun ma'lumotlar bazasini tayyorlash

**Database Configuration:**
- Database name: `brightbridge_local`
- User: `laravel_user`
- Password: `laravel_pass` (local development)
- Charset: `utf8mb4`
- Collation: `utf8mb4_unicode_ci`

**Setup Commands:**
```sql
CREATE DATABASE brightbridge_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'laravel_user'@'localhost' IDENTIFIED BY 'laravel_pass';
GRANT ALL PRIVILEGES ON brightbridge_local.* TO 'laravel_user'@'localhost';
FLUSH PRIVILEGES;
```

**Import Process:**
```bash
/opt/lampp/bin/mysql -u root -p brightbridge_local < /opt/lampp/htdocs/BrightBridge/brightbr_job.sql
```

### 4. Laravel Environment Component

**Purpose:** Laravel ilovasini local muhit uchun sozlash

**File:** `/opt/lampp/htdocs/BrightBridge/public_html/.env`

**Configuration Template:**
```env
APP_NAME="BrightBridge JobCare"
APP_ENV=local
APP_KEY=base64:GENERATED_KEY_HERE
APP_DEBUG=true
APP_URL=http://brightbridge.local

LOG_CHANNEL=stack

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=brightbridge_local
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

# AI Provider Settings (Local test keys)
AI_PROVIDER=gemini
GEMINI_API_KEY=your_test_key_here
GEMINI_MODEL=gemini-flash-latest
GEMINI_EMBEDDING_MODEL=gemini-embedding-001

# Mail settings (optional for local)
MAIL_MAILER=log
```

**Key Generation:**
```bash
cd /opt/lampp/htdocs/BrightBridge/public_html
/opt/lampp/bin/php artisan key:generate
```

### 5. Composer Dependencies Component

**Purpose:** Laravel dependencies'larni o'rnatish

**Installation:**
```bash
cd /opt/lampp/htdocs/BrightBridge/public_html
composer install --no-dev --optimize-autoloader
```

**Required Packages (from composer.json):**
- laravel/framework: 5.8.*
- guzzlehttp/guzzle: 6.5
- phpoffice/phpword: ^1.4
- smalot/pdfparser: ^2.12

### 6. File Permissions Component

**Purpose:** Laravel'ga kerakli papkalarga yozish huquqini berish

**Required Permissions:**
```bash
# Storage va cache papkalari
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Apache user'ga ownership berish
chown -R daemon:daemon storage
chown -R daemon:daemon bootstrap/cache

# Yoki current user bilan ishlash
chown -R $USER:daemon storage
chown -R $USER:daemon bootstrap/cache
```

**Critical Directories:**
- `storage/app/` - File uploads
- `storage/framework/` - Cache, sessions, views
- `storage/logs/` - Application logs
- `bootstrap/cache/` - Compiled files

## Data Models

### Environment Configuration Model

```
.env File Structure:
├── Application Settings
│   ├── APP_NAME
│   ├── APP_ENV (local)
│   ├── APP_KEY (generated)
│   ├── APP_DEBUG (true)
│   └── APP_URL (http://brightbridge.local)
├── Database Settings
│   ├── DB_CONNECTION (mysql)
│   ├── DB_HOST (127.0.0.1)
│   ├── DB_PORT (3306)
│   ├── DB_DATABASE (brightbridge_local)
│   ├── DB_USERNAME (laravel_user)
│   └── DB_PASSWORD (laravel_pass)
├── Cache & Session
│   ├── CACHE_DRIVER (file)
│   ├── SESSION_DRIVER (file)
│   └── QUEUE_CONNECTION (database)
└── AI Settings
    ├── AI_PROVIDER (gemini)
    ├── GEMINI_API_KEY
    └── GEMINI_MODEL
```

### Apache Configuration Model

```
Virtual Host Structure:
├── ServerName: brightbridge.local
├── DocumentRoot: /opt/lampp/htdocs/BrightBridge/public_html/public
├── Directory Permissions
│   ├── AllowOverride: All
│   ├── Require: all granted
│   └── RewriteEngine: On
├── PHP Settings
│   ├── upload_max_filesize: 100M
│   ├── post_max_size: 100M
│   └── memory_limit: 512M
└── Logs
    ├── ErrorLog: logs/brightbridge-error.log
    └── CustomLog: logs/brightbridge-access.log
```

## Error Handling

### Common Issues and Solutions

#### 1. XAMPP Service Start Failures

**Problem:** Apache yoki MySQL ishga tushmaydi

**Detection:**
```bash
sudo /opt/lampp/lampp status
```

**Solutions:**
- Port 80 band bo'lsa: Boshqa web server (nginx, apache2) to'xtatish
  ```bash
  sudo systemctl stop apache2
  sudo systemctl stop nginx
  ```
- Port 3306 band bo'lsa: System MySQL to'xtatish
  ```bash
  sudo systemctl stop mysql
  ```
- Permission muammosi:
  ```bash
  sudo /opt/lampp/lampp start
  ```

#### 2. Virtual Host Not Working

**Problem:** `brightbridge.local` ochilmaydi yoki 404 xato

**Detection:**
- Browser: `http://brightbridge.local`
- Check: `/opt/lampp/logs/error_log`

**Solutions:**
- `/etc/hosts` faylini tekshirish
  ```bash
  cat /etc/hosts | grep brightbridge
  ```
- Virtual host konfiguratsiyasini tekshirish
  ```bash
  cat /opt/lampp/etc/extra/httpd-vhosts.conf
  ```
- Apache'ni qayta ishga tushirish
  ```bash
  sudo /opt/lampp/lampp restart
  ```
- mod_rewrite yoqilganligini tekshirish
  ```bash
  grep "LoadModule rewrite_module" /opt/lampp/etc/httpd.conf
  ```

#### 3. Database Connection Errors

**Problem:** Laravel database'ga ulanolmaydi

**Detection:**
- Laravel error page: "SQLSTATE[HY000] [1045] Access denied"
- Log: `storage/logs/laravel.log`

**Solutions:**
- MySQL ishga tushganligini tekshirish
  ```bash
  /opt/lampp/bin/mysql -u root -p -e "SHOW DATABASES;"
  ```
- User va privileges tekshirish
  ```bash
  /opt/lampp/bin/mysql -u root -p -e "SELECT User, Host FROM mysql.user WHERE User='laravel_user';"
  ```
- `.env` faylni tekshirish
  ```bash
  cat .env | grep DB_
  ```
- Config cache tozalash
  ```bash
  /opt/lampp/bin/php artisan config:clear
  ```

#### 4. Permission Denied Errors

**Problem:** Laravel storage'ga yoza olmaydi

**Detection:**
- Error: "The stream or file could not be opened"
- Log: Permission denied errors

**Solutions:**
- Permissions to'g'rilash
  ```bash
  chmod -R 775 storage bootstrap/cache
  ```
- Ownership o'zgartirish
  ```bash
  chown -R $USER:daemon storage bootstrap/cache
  ```
- SELinux muammosi (agar mavjud bo'lsa)
  ```bash
  sudo setenforce 0  # Temporary
  ```

#### 5. Composer Dependencies Missing

**Problem:** Vendor papka yo'q yoki class not found

**Detection:**
- Error: "Class 'Illuminate\Foundation\Application' not found"
- `vendor/` papka mavjud emas

**Solutions:**
- Composer o'rnatish
  ```bash
  curl -sS https://getcomposer.org/installer | php
  sudo mv composer.phar /usr/local/bin/composer
  ```
- Dependencies o'rnatish
  ```bash
  cd /opt/lampp/htdocs/BrightBridge/public_html
  composer install
  ```

#### 6. PHP Extension Missing

**Problem:** Required PHP extension mavjud emas

**Detection:**
- Error: "PHP extension mbstring is required"

**Solutions:**
- Extension yoqish (`php.ini`)
  ```bash
  sudo nano /opt/lampp/etc/php.ini
  # Uncomment: extension=mbstring
  ```
- XAMPP'ni qayta ishga tushirish
  ```bash
  sudo /opt/lampp/lampp restart
  ```

## Testing Strategy

### Phase 1: XAMPP Installation Verification

**Test 1.1: XAMPP Services**
```bash
# Start XAMPP
sudo /opt/lampp/lampp start

# Check status
sudo /opt/lampp/lampp status

# Expected: Apache and MySQL running
```

**Test 1.2: PHP Version**
```bash
/opt/lampp/bin/php -v

# Expected: PHP 7.4.x
```

**Test 1.3: PHP Extensions**
```bash
/opt/lampp/bin/php -m | grep -E "mbstring|openssl|pdo_mysql|tokenizer|xml|ctype|json"

# Expected: All extensions listed
```

### Phase 2: Database Setup Verification

**Test 2.1: MySQL Connection**
```bash
/opt/lampp/bin/mysql -u root -p -e "SELECT VERSION();"

# Expected: MySQL 5.7.x
```

**Test 2.2: Database Creation**
```bash
/opt/lampp/bin/mysql -u root -p -e "SHOW DATABASES LIKE 'brightbridge_local';"

# Expected: brightbridge_local database exists
```

**Test 2.3: User Privileges**
```bash
/opt/lampp/bin/mysql -u laravel_user -plaravel_pass -e "USE brightbridge_local; SHOW TABLES;"

# Expected: Tables list from imported SQL
```

### Phase 3: Apache Configuration Verification

**Test 3.1: Virtual Host Syntax**
```bash
sudo /opt/lampp/bin/apachectl configtest

# Expected: Syntax OK
```

**Test 3.2: Hosts File**
```bash
ping -c 1 brightbridge.local

# Expected: 127.0.0.1 response
```

**Test 3.3: DocumentRoot Access**
```bash
ls -la /opt/lampp/htdocs/BrightBridge/public_html/public/index.php

# Expected: File exists and readable
```

### Phase 4: Laravel Application Verification

**Test 4.1: Environment File**
```bash
cd /opt/lampp/htdocs/BrightBridge/public_html
test -f .env && echo "EXISTS" || echo "MISSING"

# Expected: EXISTS
```

**Test 4.2: APP_KEY Generation**
```bash
cat .env | grep APP_KEY

# Expected: APP_KEY=base64:...
```

**Test 4.3: Config Cache**
```bash
/opt/lampp/bin/php artisan config:cache

# Expected: Configuration cached successfully
```

**Test 4.4: Database Connection Test**
```bash
/opt/lampp/bin/php artisan migrate:status

# Expected: Migration table exists or migrations list
```

### Phase 5: Web Access Verification

**Test 5.1: Homepage Load**
- Browser: `http://brightbridge.local`
- Expected: Laravel welcome page or application homepage
- Check: No 500 errors

**Test 5.2: Static Assets**
- Browser: `http://brightbridge.local/css/style.css`
- Expected: CSS file loads
- Check: 200 status code

**Test 5.3: Database Query Test**
- Browser: Navigate to jobs listing page
- Expected: Jobs displayed from database
- Check: No database connection errors

**Test 5.4: AI Features Test**
- Browser: Test AI chat widget (if visible)
- Expected: Widget loads (may need API key)
- Check: No JavaScript errors in console

### Phase 6: Error Logging Verification

**Test 6.1: Laravel Logs**
```bash
tail -f /opt/lampp/htdocs/BrightBridge/public_html/storage/logs/laravel.log

# Expected: Log file exists and writable
```

**Test 6.2: Apache Error Logs**
```bash
tail -f /opt/lampp/logs/error_log

# Expected: No critical errors
```

**Test 6.3: Apache Access Logs**
```bash
tail -f /opt/lampp/logs/brightbridge-access.log

# Expected: HTTP requests logged
```

## Implementation Notes

### Pre-requisites
- Linux OS (Ubuntu 20.04+ yoki Debian 10+ tavsiya etiladi)
- XAMPP 7.4.33 o'rnatilgan
- Composer o'rnatilgan (global)
- Git o'rnatilgan (optional, loyiha allaqachon mavjud)
- Sudo privileges

### Installation Order
1. XAMPP sozlash va ishga tushirish
2. Loyihani XAMPP htdocs'ga ko'chirish
3. Database yaratish va import qilish
4. Virtual host sozlash
5. .env fayl yaratish va sozlash
6. Composer dependencies o'rnatish
7. Permissions sozlash
8. Laravel cache va key generation
9. Testing va troubleshooting

### Security Considerations
- `.env` faylni Git'ga commit qilmaslik
- Local development uchun `APP_DEBUG=true` ishlatish mumkin
- Production API keylarni local'da ishlatmaslik
- Database parollarini oddiy qilish (local uchun)
- XAMPP'ni faqat local development uchun ishlatish (production emas!)

### Performance Optimization
- Composer: `--optimize-autoloader` flag ishlatish
- Laravel: Config va route cache ishlatish
- Apache: KeepAlive yoqish
- PHP: OPcache yoqish (production-like testing uchun)

### Backup Strategy
- Database dump olish: `mysqldump -u root -p brightbridge_local > backup.sql`
- `.env` faylni backup qilish
- `storage/` papkani backup qilish (uploads)
