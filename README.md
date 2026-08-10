# Kangwendra — Portal Berita AI Indonesia

Portal berita AI dengan fitur automated scraping, AI paraphrasing (DeepSeek), dan publishing otomatis.

## Fitur Utama

- **AI Auto-Pipeline**: Scraping artikel dari Search Engine Journal + SE Land, paraphrase via DeepSeek API, publish otomatis
- **Queue Worker**: Scraping + AI diproses di background (24/7)
- **Scheduler**: Pipeline jalan otomatis jam 03:30 WIB setiap hari
- **SEO Optimized**: Schema.org, Open Graph, Twitter Cards, RSS, llms.txt untuk AI search engines
- **GEO/AEO Ready**: Citation metadata, NewsMediaOrganization, FAQPage schemas
- **Multi-user**: Role-based access (Admin, Editor, Contributor)
- **PWA Support**: Web manifest, offline-capable

## Tech Stack

- PHP 8.2+ / Laravel 11
- MySQL 8.0+
- Queue: Database Driver
- DeepSeek API untuk AI paraphrasing
- Bootstrap 5 + Custom CSS

## Clone & Install

### Prerequisites

- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js (untuk Vite assets)

### Instalasi

```bash
# 1. Clone repo
git clone https://github.com/YOUR_USERNAME/kangwendra.git
cd kangwendra

# 2. Install dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Setup database
# Buat database MySQL, update .env dengan credentials
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_DATABASE=kangwendra
# DB_USERNAME=root
# DB_PASSWORD=your_password

# 6. Migrate & seed
php artisan migrate
php artisan db:seed

# 7. Generate Vite assets
npm install
npm run dev

# 8. Jalankan server
php artisan serve
```

### Default Login (Local)

```
Email:    admin@kangwendra.com
Password: password
```

## Production Setup

Lihat `docs/production/README.md` untuk panduan lengkap setup production server termasuk:
- Queue worker setup
- Cron scheduler
- Database production data
- Server permissions

## Queue & Scheduler

### Local Development

```bash
# Terminal 1: Queue worker
php artisan queue:work

# Terminal 2: Pipeline manual
php artisan app:auto-pipeline --max=5
```

### Production Server

```bash
# Cron: Scheduler (jalan tiap 1 menit)
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1

# Queue Worker: 24/7 background process
php /path/to/artisan queue:work --queue=default --tries=3 --timeout=900 --memory=256 --sleep=3 &
```

## Environment Variables

| Variable | Description |
|---|---|
| `APP_ENV` | `local` atau `production` |
| `APP_DEBUG` | `true` untuk development, `false` untuk production |
| `APP_URL` | URL production, contoh `https://kangwendra.com` |
| `DEEPSEEK_API_KEY` | API key dari deepseek.com |
| `QUEUE_CONNECTION` | `database` (production) atau `sync` (local) |

## SEO / AI Search

Portal ini sudah dioptimasi untuk:

- Google Search Console
- Bing Webmaster
- Perplexity AI
- ChatGPT / Claude
- Google News
- llms.txt untuk AI grounding

File sitemap: `/sitemap.xml`, `/sitemap-news.xml`, `/feed.xml`, `/llms.txt`

## Project Structure

```
kangwendra/
├── app/
│   ├── Console/Commands/     # AutoPipeline, AutoFeed
│   ├── Http/Controllers/     # Admin & Client controllers
│   ├── Jobs/               # ScrapeParaphraseJob, GenerateFromRefArticleJob, dll
│   └── Models/             # Posts, RefArticle, dll
├── database/seeders/       # Seeders untuk setup
├── docs/production/         # Panduan production server
├── resources/views/         # Blade templates
├── routes/                 # Web routes & console scheduler
└── storage/logs/           # Log files
```

## License

MIT
