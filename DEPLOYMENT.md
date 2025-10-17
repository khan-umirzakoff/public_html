# BrightBridge Deployment Guide

## Host Server Requirements

✅ **PHP 7.4+** (Laravel 5.8 uchun)
✅ **MySQL 5.7+** (allaqachon ishlab turishi kerak)
✅ **Composer** (yoki `composer.phar`)
✅ **Git** (optional, code update uchun)

## Pre-Deployment Checklist

### 1. Database Setup
Host serverda database allaqachon mavjud:
```
DB_DATABASE=brightbr_job
DB_USERNAME=brightbr_user
DB_PASSWORD=autkirov1234
```

**MUHIM:** MySQL allaqachon ishlab turadi, `deploy.sh` uni restart qilmaydi!

### 2. .env Configuration

Host serverda `.env` fayl yarating (`.env.example` dan nusxa oling):

```bash
cp .env.example .env
# Keyin .env ni tahrirlang
```

Production uchun `.env` sozlamalari:

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://brightbridge.uz  # HTTPS!

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=brightbr_job
DB_USERNAME=brightbr_user
DB_PASSWORD=autkirov1234

# AI Settings
AI_PROVIDER=gemini
GEMINI_API_KEY=your_production_key
```

**Farqlar local va production o'rtasida:**

| Setting | Local | Production |
|---------|-------|------------|
| APP_ENV | local | production |
| APP_DEBUG | true | false |
| APP_URL | http://localhost:8000 | https://brightbridge.uz |
| DB_DATABASE | brightbridge_local | brightbr_job |
| DB_USERNAME | laravel_user | brightbr_user |

### 3. File Permissions

Host serverda quyidagi papkalar writable bo'lishi kerak:
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Deployment Steps

### Option 1: Automatic Deployment (Recommended)

```bash
cd public_html
./deploy.sh
```

Bu script avtomatik:
1. ✅ .env va APP_KEY tekshiradi
2. ✅ Git pull (agar git repo bo'lsa)
3. ✅ Composer dependencies o'rnatadi
4. ✅ Database connection tekshiradi
5. ✅ Migrations bajaradi
6. ✅ Cache tozalaydi va optimize qiladi
7. ✅ Permissions sozlaydi

### Option 2: Manual Deployment

```bash
cd public_html

# 1. Update code (if using git)
git pull origin main

# 2. Install dependencies
php composer.phar install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 3. Run migrations
php artisan migrate --force

# 4. Clear and cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache

# 5. Set permissions
chmod -R 755 storage bootstrap/cache
```

## Common Issues

### 1. Session Permission Error
```
session_start(): open(/opt/lampp/temp//sess_xxx, O_RDWR) failed: Permission denied
```

**Yechim:** `deploy.sh` avtomatik hal qiladi, yoki:
```bash
sudo chmod 777 /opt/lampp/temp/
sudo rm -f /opt/lampp/temp/sess_*
```

### 2. Database Connection Failed
```
SQLSTATE[HY000] [1045] Access denied for user
```

**Yechim:** `.env` da database credentials tekshiring:
```bash
DB_HOST=127.0.0.1
DB_DATABASE=brightbr_job
DB_USERNAME=brightbr_user
DB_PASSWORD=autkirov1234
```

### 3. Storage Permission Error
```
file_put_contents(storage/logs/laravel.log): failed to open stream
```

**Yechim:**
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 4. Route Cache Error (Closure)
```
Unable to prepare route [api/user] for serialization. Uses Closure.
```

**Yechim:** Bu normal, production'da Closure route'larni controller'ga o'tkazing.

### 5. Session Headers Already Sent
```
session_start(): Cannot start session when headers already sent
```

**Sabab:** 
- Controller'da `session_start()` qo'lda chaqirilgan
- Eski kod cache'da qolgan (compiled classes)
- Laravel allaqachon session'ni boshqaradi

**Yechim:** 
1. Controller'da `session_start()` ni o'chiring
2. Cache'ni to'liq tozalang:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/views/*
rm -f bootstrap/cache/compiled.php
```

3. Yoki `deploy.sh` ishga tushiring (avtomatik tozalaydi)

**To'g'ri kod:**
```php
// ❌ Noto'g'ri:
public function main() {
    session_start();  // Laravel allaqachon boshqaradi!
    // ...
}

// ✅ To'g'ri:
public function main() {
    // Laravel automatically handles sessions
    // ...
}
```

## Post-Deployment Verification

1. **Check website:** https://brightbridge.uz
2. **Check logs:** `tail -f storage/logs/laravel.log`
3. **Test AI chat:** AI assistant ishlayaptimi?
4. **Test database:** Jobs, news, trainings ko'rinayaptimi?

### Expected Output

Successful deployment should show:
```
✅ .env file found
✅ .env configuration validated
✅ PHP 7.3.33
✅ Code updated
✅ Dependencies installed
✅ Migrations completed
✅ Application optimized
✅ Permissions set
🎉 Deployment completed successfully!
```

**Minor warnings are normal:**
- `⚠️ Cache clear skipped` - OK if cache driver is `file`
- `⚠️ View cache not found` - OK if views not cached yet
- `⚠️ Route cache skipped` - OK if routes use Closures
- `⚠️ Ownership change skipped` - OK if not running as root

## Rollback (Agar muammo bo'lsa)

```bash
# 1. Git rollback
git reset --hard HEAD~1

# 2. Restore database (if needed)
mysql -u brightbr_user -p brightbr_job < backup.sql

# 3. Clear cache
php artisan config:clear
php artisan cache:clear
```

## Security Notes

🔒 **Production'da:**
- `APP_DEBUG=false` bo'lishi kerak
- `APP_ENV=production` bo'lishi kerak
- API keys `.env` da saqlanadi (git'ga commit qilmang!)
- HTTPS ishlatiladi (`APP_URL=https://...`)

🔓 **Local'da:**
- `APP_DEBUG=true` bo'lishi mumkin
- `APP_ENV=local`
- Test API keys ishlatiladi

## Support

Muammo bo'lsa:
1. `storage/logs/laravel.log` ni tekshiring
2. `php artisan config:clear` bajaring
3. `.env` sozlamalarni tekshiring
4. MySQL ishlab turganini tekshiring: `systemctl status mysql`
