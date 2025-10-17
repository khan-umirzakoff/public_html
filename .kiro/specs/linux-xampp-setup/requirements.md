# Requirements Document

## Introduction

BrightBridge (JobCare) loyihasi real serverdan olingan va Windows/Laragon muhitida ishlagan. Endi loyihani Linux muhitida XAMPP 7.4.33 yordamida sozlash va ishga tushirish kerak. Loyiha Laravel 5.8 frameworkida yozilgan va PHP 7.4, MySQL 5.7 talab qiladi. Asosiy kod fayllariga tegmasdan, faqat muhit sozlamalarini o'zgartirish kerak.

## Glossary

- **XAMPP**: Linux uchun Apache, MySQL, PHP va Perl paketini o'z ichiga olgan web server muhiti
- **Laravel_App**: BrightBridge/JobCare Laravel 5.8 ilovasi
- **Environment_Config**: .env fayli va boshqa muhit sozlamalari
- **Database_Instance**: MySQL 5.7 ma'lumotlar bazasi
- **Apache_Server**: XAMPP ichidagi Apache web server
- **Virtual_Host**: Apache'da maxsus domen sozlamasi (masalan, brightbridge.local)

## Requirements

### Requirement 1

**User Story:** Developer sifatida, men XAMPP muhitini to'g'ri sozlashim kerak, shunda Laravel ilovasi ishlashi uchun barcha kerakli komponentlar mavjud bo'lsin

#### Acceptance Criteria

1. WHEN XAMPP 7.4.33 o'rnatilgan bo'lsa, THE System SHALL PHP 7.4 versiyasini taqdim etsin
2. WHEN XAMPP ishga tushirilsa, THE Apache_Server SHALL 80-portda ishga tushsin
3. WHEN XAMPP ishga tushirilsa, THE Database_Instance SHALL 3306-portda ishga tushsin
4. THE System SHALL PHP kengaytmalarini yoqish imkonini bersin (mbstring, openssl, pdo_mysql, tokenizer, xml, ctype, json)
5. THE System SHALL composer paket menejerini ishlatish imkonini bersin

### Requirement 2

**User Story:** Developer sifatida, men database'ni import qilishim va sozlashim kerak, shunda Laravel ilovasi ma'lumotlar bazasiga ulansin

#### Acceptance Criteria

1. WHEN MySQL ishga tushirilsa, THE Database_Instance SHALL brightbridge_local nomli database yaratish imkonini bersin
2. WHEN SQL dump fayli mavjud bo'lsa, THE System SHALL faylni import qilish imkonini bersin
3. THE Database_Instance SHALL Laravel_App uchun user va parol yaratish imkonini bersin
4. WHEN database sozlamalari to'g'ri bo'lsa, THE Laravel_App SHALL Database_Instance'ga muvaffaqiyatli ulansin
5. THE Database_Instance SHALL UTF-8 charset'ni qo'llab-quvvatlashi kerak

### Requirement 3

**User Story:** Developer sifatida, men Apache virtual host sozlashim kerak, shunda loyihani maxsus domen orqali ochish mumkin bo'lsin

#### Acceptance Criteria

1. THE Apache_Server SHALL virtual host konfiguratsiyasini qabul qilishi kerak
2. WHEN virtual host sozlansa, THE System SHALL brightbridge.local domenini Laravel_App'ning public papkasiga yo'naltirishi kerak
3. THE Apache_Server SHALL mod_rewrite modulini yoqish imkonini bersin
4. WHEN /etc/hosts fayli o'zgartirilsa, THE System SHALL brightbridge.local domenini 127.0.0.1 ga yo'naltirishi kerak
5. THE Apache_Server SHALL .htaccess fayllarini qayta ishlashi kerak

### Requirement 4

**User Story:** Developer sifatida, men Laravel ilovasi uchun environment sozlamalarini to'g'ri konfiguratsiya qilishim kerak

#### Acceptance Criteria

1. THE Laravel_App SHALL .env faylini o'qish va ishlatish imkoniga ega bo'lsin
2. WHEN .env fayli yaratilsa, THE Environment_Config SHALL local muhit uchun to'g'ri sozlamalarni o'z ichiga olishi kerak
3. THE Environment_Config SHALL database ulanish ma'lumotlarini to'g'ri ko'rsatishi kerak
4. THE Laravel_App SHALL APP_KEY generatsiya qilish imkonini bersin
5. THE Environment_Config SHALL AI provider sozlamalarini o'z ichiga olishi kerak

### Requirement 5

**User Story:** Developer sifatida, men Laravel dependencies'larni o'rnatishim va cache'larni sozlashim kerak

#### Acceptance Criteria

1. WHEN composer mavjud bo'lsa, THE System SHALL vendor dependencies'larni o'rnatish imkonini bersin
2. THE Laravel_App SHALL storage va bootstrap/cache papkalariga yozish huquqiga ega bo'lishi kerak
3. WHEN permissions to'g'ri sozlansa, THE Apache_Server SHALL storage papkalariga kirish huquqiga ega bo'lsin
4. THE Laravel_App SHALL config cache yaratish imkonini bersin
5. THE Laravel_App SHALL route cache yaratish imkonini bersin

### Requirement 6

**User Story:** Developer sifatida, men loyihani test qilishim va xatolarni tuzatishim kerak

#### Acceptance Criteria

1. WHEN brauzerda brightbridge.local ochilsa, THE Laravel_App SHALL asosiy sahifani ko'rsatishi kerak
2. IF xato yuz bersa, THEN THE Laravel_App SHALL xato xabarini ko'rsatishi kerak
3. THE System SHALL Laravel log fayllarini yozish imkonini bersin
4. WHEN database ulanishi test qilinsa, THE Laravel_App SHALL ulanish holatini ko'rsatishi kerak
5. THE Laravel_App SHALL AI funksiyalarini test qilish imkonini bersin
