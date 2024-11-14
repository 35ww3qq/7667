# Backlink Management System

Modern ve güvenli backlink yönetim sistemi.

## Özellikler

- Admin ve müşteri panelleri
- Gelişmiş güvenlik sistemi
- Kredi sistemi
- Site doğrulama
- Backlink takibi
- Detaylı raporlama

## Kurulum

1. Dosyaları sunucunuza yükleyin
2. database.sql dosyasını veritabanınıza import edin
3. config.php dosyasını düzenleyin:
   - Veritabanı bilgilerini güncelleyin
   - Güvenlik ayarlarını yapılandırın
   - Email ayarlarını yapılandırın
4. Composer paketlerini yükleyin:
   composer install
5. Cron görevini ekleyin:
   0 * * * * php /path/to/cron/check_backlinks.php

## Güvenlik

- SQL injection koruması
- XSS & CSRF koruması
- Rate limiting
- Brute force koruması
- IP ve kullanıcı takibi
- Güvenlik logları
- Admin uyarı sistemi

## Admin Panel

Admin paneline erişmek için:
yourdomain.com/panel/admin/

## Müşteri Panel

Müşteri paneline erişmek için:
yourdomain.com/panel/customer/