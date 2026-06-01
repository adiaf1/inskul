# Laravel Multi-Role User Management

Project ini adalah sistem manajemen pengguna berbasis Laravel dengan autentikasi menggunakan Laravel Breeze dan manajemen role menggunakan Spatie Laravel Permission.

## Requirement

- PHP 8.3 atau lebih baru
- Composer
- Node.js dan npm
- MySQL atau MariaDB

## Instalasi

Jalankan langkah berikut setelah clone project.

### 1. Install dependency PHP

```bash
composer install
```

### 2. Install dependency frontend

```bash
npm install
```

### 3. Copy dan konfigurasi `.env`

```bash
cp .env.example .env
```

Sesuaikan koneksi database di `.env`:

```env
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Generate application key

```bash
php artisan key:generate
```

### 5. Jalankan migrasi dan seeder

```bash
php artisan migrate --seed
```

Seeder akan membuat 3 role (`admin`, `editor`, `guest`) dan masing-masing satu user default:

| Role | Email | Password |
| --- | --- | --- |
| Admin | admin@mail.com | password |
| Editor | editor@mail.com | password |
| Guest | guest@mail.com | password |

Jika hanya ingin menjalankan seeder role/user:

```bash
php artisan db:seed --class=RoleSeeder
```

### 6. Build asset frontend

Untuk development:

```bash
npm run dev
```

Untuk production build:

```bash
npm run build
```

### 7. Jalankan aplikasi

```bash
php artisan serve
```

Buka aplikasi di browser:

```text
http://localhost:8000
```

## Fitur

- Authentication scaffold dari Laravel Breeze
- Multi-role dengan Spatie Laravel Permission
- Redirect dashboard berdasarkan role
- Dashboard terpisah untuk admin, editor, dan guest
- Manajemen user khusus admin
- Notifikasi pada proses user management

## Tech Stack

- Laravel 13
- PHP 8.3
- Laravel Breeze
- Laravel Sanctum
- Spatie Laravel Permission
- Vite
- Tailwind CSS
- Bootstrap UI pada halaman manajemen user

## Verifikasi

Command yang dapat dipakai untuk memastikan project berjalan setelah update dependency:

```bash
composer validate --strict
composer audit
php artisan test
npm run build
```

## Catatan

Jika project menggunakan upload file publik, jalankan:

```bash
php artisan storage:link
```

Untuk production, pastikan `.env` menggunakan:

```env
APP_ENV=production
APP_DEBUG=false
```

## Lisensi

MIT License.
