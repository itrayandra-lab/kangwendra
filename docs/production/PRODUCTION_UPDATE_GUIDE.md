# Production Server — Manual Update Guide

## Yang Perlu Di-Update di Production

Ada **2 tabel** yang perlu Lo ubah di database production:

| No | Tabel | File SQL | Apa yang Berubah |
|----|-------|---------|----------------|
| 1 | `users` + `model_has_roles` | `PRODUCTION_USERS.sql` | Hapus semua user, buat user baru `it@kangwendra.com` |
| 2 | `web_identities` | `PRODUCTION_WEB_IDENTITY.sql` | Update info portal |

---

## ⚠️ PERINGATAN

- **Backup database dulu** sebelum import SQL apapun
- SQL ini **TIDAK mengubah**: posts, categories, tags, articles, scraping data, queue jobs, dll
- Hanya mengubah **user credentials** dan **web identity info**

---

## Step 1 — Backup Database (WAJIB)

Lewat phpMyAdmin:
1. Pilih database `kangwendra`
2. Klik tab **"Export"**
3. Klik **"Go"**
4. Simpan file backup

---

## Step 2 — Update Users (Wajib)

1. Pilih database `kangwendra`
2. Klik tab **"SQL"**
3. Copy-paste isi file **`PRODUCTION_USERS.sql`**
4. Klik **"Go"**

Hasil:
- Semua user dihapus
- User baru dibuat:
  - **Email:** `it@kangwendra.com`
  - **Password:** `@R4y4ndr4`
  - **Role:** Admin

---

## Step 3 — Update Web Identity

1. Pilih database `kangwendra`
2. Klik tab **"SQL"**
3. Copy-paste isi file **`PRODUCTION_WEB_IDENTITY.sql`**
4. Pilih bagian **(A)** — karena database sudah ada datanya dari export local
5. Klik **"Go"**

---

## Step 4 — Login ke Admin Panel

1. Buka `https://kangwendra.com/portal/login`
2. Login dengan:
   - **Email:** `it@kangwendra.com`
   - **Password:** `@R4y4ndr4`
3. **WAJIB** — Ganti password setelah login

---

## Tabel yang TIDAK Berubah

| Tabel | Status |
|-------|--------|
| `posts` | ✅ Tetap sama |
| `post_categories` | ✅ Tetap sama |
| `post_tags` | ✅ Tetap sama |
| `ref_articles` | ✅ Tetap sama |
| `research_recommendations` | ✅ Tetap sama |
| `scraper_configs` | ✅ Tetap sama |
| `roles` & `permissions` | ✅ Tetap sama |
| `jobs` & `failed_jobs` | ✅ Tetap sama |
| `menus` | ✅ Tetap sama |
| `videos` | ✅ Tetap sama |
| `albums` | ✅ Tetap sama |
| `pages` | ✅ Tetap sama |
| `information` | ✅ Tetap sama |
| `ads` | ✅ Tetap sama |

---

## Kalau Ada Error

### "Cannot truncate table" atau "Foreign key constraint"
Jalankan ini dulu di SQL:
```sql
SET FOREIGN_KEY_CHECKS = 0;
```
Lalu jalankan SQL normally.

### "Table doesn't exist"
Berarti tabel tersebut tidak ada di production. Lewati saja.

### User login tidak bisa
Pastikan:
1. `model_has_roles` sudah terisi (user id=1, role_id=1)
2. Password hash sudah benar

---

## Setup Queue Worker di Server (Setelah SQL Import)

SSH ke server, jalankan:

```bash
cd /www/wwwroot/kangwendra.com
php artisan db:seed --class=ProductionDataSeeder
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```
