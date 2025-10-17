# Implementation Plan

- [x] 1. XAMPP muhitini tekshirish va sozlash
  - XAMPP 7.4.33 o'rnatilganligini tekshirish va kerakli xizmatlarni ishga tushirish
  - PHP 7.4 versiyasini va kerakli extensionlarni tekshirish
  - Apache va MySQL xizmatlarini ishga tushirish va statusini tekshirish
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

- [x] 1.1 XAMPP xizmatlarini ishga tushirish
  - `sudo /opt/lampp/lampp start` buyrug'i bilan XAMPP'ni ishga tushirish
  - Apache va MySQL xizmatlarining holatini tekshirish
  - Agar portlar band bo'lsa, system Apache2 va MySQL'ni to'xtatish
  - _Requirements: 1.2, 1.3_

- [x] 1.2 PHP versiyasi va extensionlarni tekshirish
  - `/opt/lampp/bin/php -v` bilan PHP 7.4 versiyasini tasdiqlash
  - `/opt/lampp/bin/php -m` bilan kerakli extensionlar (mbstring, openssl, pdo_mysql, tokenizer, xml, ctype, json, fileinfo, gd) mavjudligini tekshirish
  - Agar extension yo'q bo'lsa, `/opt/lampp/etc/php.ini` faylida yoqish
  - _Requirements: 1.1, 1.4_

- [x] 1.3 PHP konfiguratsiyasini sozlash
  - `/opt/lampp/etc/php.ini` faylini ochish va quyidagi sozlamalarni o'zgartirish:
    - `upload_max_filesize = 100M`
    - `post_max_size = 100M`
    - `max_execution_time = 600`
    - `max_input_time = 600`
    - `memory_limit = 512M`
    - `display_errors = On`
    - `error_reporting = E_ALL`
  - XAMPP'ni qayta ishga tushirish: `sudo /opt/lampp/lampp restart`
  - _Requirements: 1.1_

- [x] 2. Loyihani XAMPP htdocs papkasiga ko'chirish
  - BrightBridge loyihasini `/opt/lampp/htdocs/` papkasiga ko'chirish yoki symlink yaratish
  - Papka strukturasini tekshirish: `public_html/` papka mavjudligini tasdiqlash
  - _Requirements: 1.5_

- [x] 2.1 Loyihani ko'chirish yoki symlink yaratish
  - Agar loyiha boshqa joyda bo'lsa, symlink yaratish: `sudo ln -s ~/Desktop/BrightBridge /opt/lampp/htdocs/BrightBridge`
  - Yoki to'g'ridan-to'g'ri ko'chirish: `sudo cp -r ~/Desktop/BrightBridge /opt/lampp/htdocs/`
  - Ownership tekshirish: `ls -la /opt/lampp/htdocs/BrightBridge`
  - _Requirements: 1.5_

- [x] 3. MySQL database yaratish va import qilish
  - MySQL'ga ulanish va `brightbridge_local` database yaratish
  - Database user yaratish va privileges berish
  - SQL dump faylini import qilish
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

- [x] 3.1 Database va user yaratish
  - MySQL'ga root sifatida ulanish: `/opt/lampp/bin/mysql -u root -p`
  - Database yaratish: `CREATE DATABASE brightbridge_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
  - User yaratish: `CREATE USER 'laravel_user'@'localhost' IDENTIFIED BY 'laravel_pass';`
  - Privileges berish: `GRANT ALL PRIVILEGES ON brightbridge_local.* TO 'laravel_user'@'localhost';`
  - `FLUSH PRIVILEGES;` bajarish
  - _Requirements: 2.1, 2.3, 2.5_

- [x] 3.2 SQL dump faylini import qilish
  - SQL dump faylini topish: `brightbr_job.sql`
  - Import qilish: `/opt/lampp/bin/mysql -u root -p brightbridge_local < /opt/lampp/htdocs/BrightBridge/brightbr_job.sql`
  - Import muvaffaqiyatli bo'lganligini tekshirish: `/opt/lampp/bin/mysql -u laravel_user -plaravel_pass -e "USE brightbridge_local; SHOW TABLES;"`
  - _Requirements: 2.2, 2.4_

- [x] 4. Apache virtual host sozlash
  - Virtual host konfiguratsiya faylini yaratish
  - `/etc/hosts` faylini tahrirlash
  - Apache'ni qayta ishga tushirish va tekshirish
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

- [x] 4.1 Virtual host konfiguratsiyasini yaratish
  - `/opt/lampp/etc/extra/httpd-vhosts.conf` faylini ochish
  - Quyidagi virtual host konfiguratsiyasini qo'shish:
    ```apache
    <VirtualHost *:80>
        ServerName brightbridge.local
        ServerAlias www.brightbridge.local
        DocumentRoot "/opt/lampp/htdocs/BrightBridge/public_html/public"
        
        <Directory "/opt/lampp/htdocs/BrightBridge/public_html/public">
            Options Indexes FollowSymLinks MultiViews
            AllowOverride All
            Require all granted
            
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ index.php [QSA,L]
        </Directory>
        
        ErrorLog "logs/brightbridge-error.log"
        CustomLog "logs/brightbridge-access.log" combined
        
        php_value upload_max_filesize 100M
        php_value post_max_size 100M
        php_value max_execution_time 600
        php_value memory_limit 512M
    </VirtualHost>
    ```
  - _Requirements: 3.1, 3.2, 3.5_

- [x] 4.2 httpd.conf'da virtual hosts'ni yoqish
  - `/opt/lampp/etc/httpd.conf` faylini ochish
  - `Include etc/extra/httpd-vhosts.conf` qatorini uncomment qilish (agar comment bo'lsa)
  - mod_rewrite modulini tekshirish: `LoadModule rewrite_module modules/mod_rewrite.so` uncomment bo'lishi kerak
  - _Requirements: 3.3_

- [x] 4.3 /etc/hosts faylini tahrirlash
  - `/etc/hosts` faylini sudo bilan ochish: `sudo nano /etc/hosts`
  - Quyidagi qatorni qo'shish: `127.0.0.1    brightbridge.local`
  - Faylni saqlash va yopish
  - Tekshirish: `ping -c 1 brightbridge.local` (127.0.0.1 ga javob berishi kerak)
  - _Requirements: 3.4_

- [x] 4.4 Apache konfiguratsiyasini test qilish va qayta ishga tushirish
  - Syntax tekshirish: `sudo /opt/lampp/bin/apachectl configtest` (Syntax OK bo'lishi kerak)
  - Apache'ni qayta ishga tushirish: `sudo /opt/lampp/lampp restart`
  - Apache statusini tekshirish: `sudo /opt/lampp/lampp status`
  - _Requirements: 3.1, 3.5_

- [x] 5. Laravel environment (.env) faylini yaratish va sozlash
  - `.env.example` faylidan `.env` yaratish
  - Database va boshqa sozlamalarni to'g'rilash
  - APP_KEY generatsiya qilish
  - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

- [x] 5.1 .env faylini yaratish
  - `public_html` papkasiga o'tish: `cd /opt/lampp/htdocs/BrightBridge/public_html`
  - `.env` faylini yaratish: `cp .env.example .env`
  - `.env` faylini ochish: `nano .env`
  - _Requirements: 4.1, 4.2_

- [x] 5.2 .env faylida database sozlamalarini o'zgartirish
  - `.env` faylida quyidagi qatorlarni topish va o'zgartirish:
    - `APP_ENV=local`
    - `APP_DEBUG=true`
    - `APP_URL=http://brightbridge.local`
    - `DB_CONNECTION=mysql`
    - `DB_HOST=127.0.0.1`
    - `DB_PORT=3306`
    - `DB_DATABASE=brightbridge_local`
    - `DB_USERNAME=laravel_user`
    - `DB_PASSWORD=laravel_pass`
  - Faylni saqlash
  - _Requirements: 4.2, 4.3_

- [x] 5.3 .env faylida AI va boshqa sozlamalarni tekshirish
  - AI provider sozlamalarini tekshirish (GEMINI_API_KEY, AI_PROVIDER)
  - Cache va session driver'larni tekshirish (file bo'lishi kerak local uchun)
  - Mail driver'ni `log` ga o'zgartirish (local testing uchun)
  - _Requirements: 4.5_

- [x] 5.4 APP_KEY generatsiya qilish
  - Artisan buyrug'ini bajarish: `/opt/lampp/bin/php artisan key:generate`
  - `.env` faylida `APP_KEY` qiymati paydo bo'lganligini tekshirish
  - _Requirements: 4.4_

- [x] 6. Composer dependencies o'rnatish
  - Composer o'rnatilganligini tekshirish
  - Laravel dependencies'larni o'rnatish
  - Autoload fayllarini generatsiya qilish
  - _Requirements: 1.5, 5.1, 5.2_

- [x] 6.1 Composer mavjudligini tekshirish
  - `composer --version` buyrug'ini bajarish
  - Agar composer o'rnatilmagan bo'lsa, o'rnatish:
    ```bash
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    ```
  - _Requirements: 1.5_

- [x] 6.2 Laravel dependencies o'rnatish
  - `public_html` papkasiga o'tish: `cd /opt/lampp/htdocs/BrightBridge/public_html`
  - Dependencies o'rnatish: `composer install --no-dev --optimize-autoloader`
  - `vendor/` papka yaratilganligini tekshirish
  - _Requirements: 5.1, 5.2_

- [x] 7. File permissions sozlash
  - Storage va cache papkalariga yozish huquqini berish
  - Apache user'ga ownership berish yoki to'g'rilash
  - _Requirements: 5.2, 5.3, 5.4_

- [x] 7.1 Storage va cache papkalariga permissions berish
  - `public_html` papkasida turgan holda:
    ```bash
    chmod -R 775 storage
    chmod -R 775 bootstrap/cache
    ```
  - Agar papkalar mavjud bo'lmasa, yaratish kerak
  - _Requirements: 5.2, 5.3_

- [x] 7.2 Ownership sozlash
  - Apache user'ni aniqlash (odatda `daemon` yoki `nobody` XAMPP'da)
  - Ownership o'zgartirish:
    ```bash
    sudo chown -R $USER:daemon storage
    sudo chown -R $USER:daemon bootstrap/cache
    ```
  - Yoki faqat current user bilan ishlash: `sudo chown -R $USER:$USER storage bootstrap/cache`
  - _Requirements: 5.3, 5.4_

- [x] 8. Laravel cache va konfiguratsiyalarni sozlash
  - Config cache yaratish
  - Route cache yaratish (optional)
  - View cache tozalash
  - _Requirements: 5.4, 5.5_

- [x] 8.1 Laravel cache buyruqlarini bajarish
  - Config cache: `/opt/lampp/bin/php artisan config:cache`
  - Cache tozalash (agar kerak bo'lsa): `/opt/lampp/bin/php artisan cache:clear`
  - View cache tozalash: `/opt/lampp/bin/php artisan view:clear`
  - _Requirements: 5.4, 5.5_

- [x] 9. Ilovani test qilish va troubleshooting
  - Brauzerda `http://brightbridge.local` ochish
  - Database ulanishini tekshirish
  - Log fayllarni tekshirish
  - Xatolarni tuzatish
  - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

- [x] 9.1 Brauzerda ilovani ochish va asosiy test
  - Brauzerda `http://brightbridge.local` manzilini ochish
  - Homepage yuklanishini tekshirish (500 yoki 404 xato bo'lmasligi kerak)
  - Agar xato bo'lsa, Apache error log'ni tekshirish: `tail -f /opt/lampp/logs/error_log`
  - Laravel log'ni tekshirish: `tail -f storage/logs/laravel.log`
  - _Requirements: 6.1, 6.2_

- [x] 9.2 Database ulanishini test qilish
  - Artisan buyrug'i bilan test: `/opt/lampp/bin/php artisan migrate:status`
  - Brauzerda jobs yoki news sahifalarini ochish (database'dan ma'lumot ko'rsatishi kerak)
  - Agar xato bo'lsa, `.env` faylida database sozlamalarni qayta tekshirish
  - Config cache'ni tozalash: `/opt/lampp/bin/php artisan config:clear`
  - _Requirements: 6.4_

- [x] 9.3 Static assets (CSS, JS, images) yuklanishini tekshirish
  - Brauzerda `http://brightbridge.local/css/style.css` ochish
  - Browser Developer Tools (F12) bilan Network tab'ni ochish
  - CSS, JS va image fayllar 200 status code bilan yuklanishini tekshirish
  - Agar 404 bo'lsa, DocumentRoot va symlink'larni tekshirish
  - _Requirements: 6.1_

- [x] 9.4 AI funksiyalarini test qilish (optional)
  - Agar AI chat widget ko'rinsa, ochish va test qilish
  - `.env` faylida `GEMINI_API_KEY` yoki `OPENAI_API_KEY` to'g'ri sozlanganligini tekshirish
  - Agar API key yo'q bo'lsa, test key olish yoki bu qadamni keyinroqqa qoldirish
  - _Requirements: 6.5_

- [x] 9.5 Log fayllar va permissions'ni yakuniy tekshirish
  - Laravel log yozilayotganligini tekshirish: `ls -la storage/logs/`
  - Apache access log: `tail -f /opt/lampp/logs/brightbridge-access.log`
  - Barcha kerakli papkalar writable ekanligini tasdiqlash
  - _Requirements: 6.3_

- [ ] 10. Qo'shimcha optimizatsiya va dokumentatsiya
  - Performance optimizatsiya sozlamalari
  - Backup strategiyasini hujjatlashtirish
  - Troubleshooting guide yaratish
  - _Requirements: All_

- [ ] 10.1 OPcache va performance sozlamalari
  - `/opt/lampp/etc/php.ini` da OPcache yoqish (production-like testing uchun)
  - Apache KeepAlive sozlamalarini tekshirish
  - Laravel route cache yaratish: `/opt/lampp/bin/php artisan route:cache`
  - _Requirements: All_

- [ ] 10.2 Backup va recovery hujjatini yaratish
  - Database backup buyrug'ini hujjatlashtirish: `mysqldump -u root -p brightbridge_local > backup.sql`
  - `.env` va `storage/` papka backup strategiyasini yozish
  - Recovery jarayonini hujjatlashtirish
  - _Requirements: All_

- [ ] 10.3 Troubleshooting guide yaratish
  - Umumiy xatolar va ularning yechimlarini hujjatlashtirish
  - Port conflicts, permission errors, database connection issues
  - Quick reference commands ro'yxatini yaratish
  - _Requirements: All_
