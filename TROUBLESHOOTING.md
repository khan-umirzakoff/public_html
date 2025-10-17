# Session "Headers Already Sent" Muammosi - Chuqur Tahlil

## Muammo
```
session_start(): Cannot start session when headers already sent
```

## Asosiy Sabablar (Internet Research)

### 1. **Output Before Session Start**
PHP'da session_start() dan oldin **HECH QANDAY** output bo'lmasligi kerak:
- Echo, print
- HTML kod
- Bo'sh joylar (whitespace)
- BOM (Byte Order Mark)

### 2. **BOM (Byte Order Mark) Muammosi**
UTF-8 BOM faylda ko'rinmas belgilar qoldiradi.

**Tekshirish:**
```bash
# Serverda
cd ~/public_html
head -c 3 app/Http/Controllers/IndexController.php | od -An -tx1
```

Agar `ef bb bf` ko'rsatsa - BOM bor!

**Yechim:**
```bash
# BOM ni o'chirish
sed -i '1s/^\xEF\xBB\xBF//' app/Http/Controllers/IndexController.php
```

### 3. **PHP Closing Tag Muammosi**
PHP faylning oxirida `?>` bo'lsa, undan keyin whitespace muammo yaratishi mumkin.

**Yechim:** PHP fayllarning oxirida `?>` qo'ymang (Laravel best practice).

### 4. **Include/Require Fayllar**
Agar boshqa fayllar include qilinsa, ularda ham output bo'lmasligi kerak.

**Tekshirish:**
```bash
# Barcha PHP fayllarni tekshirish
find app/ -name "*.php" -exec head -c 3 {} \; -print | od -An -tx1
```

### 5. **Error Messages**
PHP error/warning messages ham output hisoblanadi.

**Yechim - .env da:**
```
APP_DEBUG=false
APP_LOG_LEVEL=error
```

### 6. **Output Buffering**
PHP.ini da output_buffering o'chirilgan bo'lishi mumkin.

**Tekshirish:**
```bash
php -i | grep output_buffering
```

**Yechim - php.ini da:**
```
output_buffering = 4096
```

### 7. **Middleware Tartib Muammosi**
Laravel'da StartSession middleware boshqa middleware'lardan oldin bo'lishi kerak.

**Tekshirish:** `app/Http/Kernel.php`
```php
protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class, // Bu birinchi bo'lishi kerak
        // ...
    ],
];
```

### 8. **Double Session Start**
Agar Laravel middleware session'ni boshqarsa, qo'lda session_start() kerak emas.

**Yechim:** Middleware ishlatilsa, session_start() ni o'chirish kerak.

### 9. **Apache/Nginx Configuration**
Server configuration'da output buffering sozlamalari.

**Apache .htaccess:**
```apache
php_flag output_buffering on
php_value output_buffering 4096
```

### 10. **Compiled Files Cache**
OPcache yoki compiled files eski versiyani saqlashi mumkin.

**Yechim:**
```bash
# OPcache tozalash
php artisan optimize:clear

# Yoki qo'lda
rm -rf bootstrap/cache/*.php
```

## Bizning Holatda Tekshirish Kerak

1. **BOM tekshirish:**
```bash
cd ~/public_html
head -c 3 app/Http/Controllers/IndexController.php | od -An -tx1
```

2. **PHP closing tag tekshirish:**
```bash
tail -5 app/Http/Controllers/IndexController.php
```

3. **Output buffering tekshirish:**
```bash
php -i | grep output_buffering
```

4. **Error log tekshirish:**
```bash
tail -50 storage/logs/laravel.log
```

5. **Apache error log:**
```bash
tail -50 /var/log/httpd/error_log
# yoki
tail -50 /usr/local/apache/logs/error_log
```

## Muvaqqat Yechim (Workaround)

Agar hech narsa ishlamasa, `ob_start()` ishlatish mumkin:

```php
public function main()
{
    ob_start(); // Output buffering yoqish
    session_start();
    
    // ... kod ...
    
    ob_end_flush(); // Oxirida
}
```

Lekin bu **to'g'ri yechim emas**, faqat muvaqqat!

## To'g'ri Yechim

Laravel'da session_start() ishlatmaslik kerak. Laravel middleware avtomatik boshqaradi.

**Agar $_SESSION ishlatilsa:**
```php
// ❌ Noto'g'ri:
session_start();
$_SESSION['user_id'] = 123;

// ✅ To'g'ri (Laravel):
session(['user_id' => 123]);
$userId = session('user_id');
```

## Keyingi Qadamlar

1. Xato loglarini ko'rsating
2. BOM tekshiring
3. Output buffering holatini tekshiring
4. PHP versiyasi va sozlamalarini tekshiring
