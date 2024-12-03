## erd
https://github.com/mas-yan/crm-test/blob/master/erd.png

## Cara Install

### Langkah Instalasi

1. Clone Repo, pada terminal
2. `cd crm-test`
3. `composer install`
4. `cp .env.example .env`
5. `php artisan key:generate`
5. `php artisan key:generate --env=testing` untuk testing
6. Buat Database dengan nama `crm` atau sesuai keinginan anda
7. `php artisan migrate --seed`
8. `php artisan migrate --seed --env-testing`
9. `php artisan jwt:secret`
10. `php artisan jwt:secret --env=testing` untuk testing
11. `php artisan test`

### Menjalankan Server

`php artisan serve`
- Akses http://127.0.0.1:8000/docs/api#/ atau periksa routes pada api.php untuk dokumentasi api
- ganti email dengan `superAdmin@example.com` dan password `password`

### note
saya menggunakan library seperti spatie untuk role, dan scramble untuk dokumentasi api
