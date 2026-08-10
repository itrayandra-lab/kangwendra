# Production Server Setup Guide

## Prerequisites

1. A VPS / Server dengan:
   - PHP 8.2+
   - MySQL 8.0+
   - Composer
   - SSH / Terminal access

2. Domain sudah pointing ke server

---

## Step 1 — Clone & Install

```bash
# Clone repo
git clone https://github.com/YOUR_USERNAME/kangwendra.git
cd kangwendra

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate
```

---

## Step 2 — Setup Database

```bash
# Buat database MySQL
mysql -u root -p
CREATE DATABASE kangwendra CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
EXIT

# Update .env dengan credentials database
nano .env
```

Set dalam `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kangwendra
DB_USERNAME=root
DB_PASSWORD=your_password
```

---

## Step 3 — Migrate & Seed

```bash
# Install fresh database
php artisan migrate

# Seed admin user
php artisan db:seed --class=AdminUserSeeder

# Default login:
# Email:    admin@kangwendra.com
# Password: password
```

---

## Step 4 — Setup Production Credentials

**Opsi A — via Laravel Seeder:**
```bash
php artisan db:seed --class=ProductionDataSeeder
# Login: it@kangwendra.com / @R4y4ndr4
```

**Opsi B — via SQL (jika database sudah ada):**
```bash
mysql -u root -p kangwendra < docs/production/production_data.sql
```

---

## Step 5 — Configure .env Production

Update `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://kangwendra.com

QUEUE_CONNECTION=database
CACHE_STORE=file

DEEPSEEK_API_KEY=your_deepseek_api_key
```

---

## Step 6 — Permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data /path/to/kangwendra
```

---

## Step 7 — Queue Worker (Wajib!)

Setup queue worker via cron atau process manager:

```bash
# Cron: jalankan scheduler
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1

# Process Manager: jalankan queue worker 24/7
php /path/to/artisan queue:work --queue=default --tries=3 --timeout=900 --memory=256 --sleep=3 &
```

---

## Step 8 — Verify Installation

```bash
# Cek semua routes
php artisan route:list

# Cek scheduler
php artisan schedule:list

# Cek queue worker
php artisan queue:monitor
```

---

## Troubleshooting

### Blank white page
- Cek `APP_KEY` sudah di-generate
- Cek `storage/logs/laravel.log`

### Database connection error
- Cek `.env` DB credentials benar
- Cek MySQL service jalan

### 500 Error
- Set `APP_DEBUG=true` di `.env` untuk lihat error
- Cek `storage/logs/laravel.log`

---

## Maintenance

Untuk maintenance mode:
```bash
php artisan down
```

Untuk kembali online:
```bash
php artisan up
```
