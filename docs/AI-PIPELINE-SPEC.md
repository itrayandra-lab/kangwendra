# Kangwendra AI Pipeline — Current State

**Versi:** 2.0
**Tanggal Update:** 29 Juli 2026
**Status:** Active & Running

---

## Ringkasan

Pipeline AI di Kangwendra berjalan otomatis dari scraping hingga publish **tanpa scheduler publish**. Post langsung `active` saat dibuat oleh AI.

---

## Alur Pipeline

```
03:30 WIB  → app:auto-pipeline (via scheduler, 1x/hari)
               ↓
             KeywordResearchJob
               ├─ Scrap sitemap_index.xml SEJ + SEL
               ├─ Confidence scoring (threshold: 45%)
               ├─ Skip jika URL sudah ada di ref_articles
               └─ Simpan ke research_recommendations
               ↓
             ScrapeParaphraseJob × max 5 (via queue worker)
               ├─ Fetch article dari URL
               ├─ Validate (AI keywords, image, age < 1 tahun)
               ├─ DeepSeek paraphrase → artikel baru Bahasa Indonesia
               ├─ Normalize content (hapus \n literal)
               ├─ Auto-assign slot publish (08:00 / 13:00 / 16:00)
               ├─ Save post: status='active', published_at=slot
               └─ AI learning (approval feedback)

Setiap menit → queue worker proses jobs dari queue
```

---

## Publish Slot

Post AI dibuat langsung dengan `status='active'`. `published_at` menentukan kapan post muncul di beranda.

| Slot | `published_at` | Keterangan |
|------|---------------|-----------|
| #1 | HH:MM:SS → 08:00 | Slot pagi |
| #2 | HH:MM:SS → 13:00 | Slot siang |
| #3 | HH:MM:SS → 16:00 | Slot sore |
| Overflow (>3 post) | Hari ini 08:00 | Langsung publish |

**Overflow:** Kalau semua slot penuh, post diarahkan ke 08:00 hari yang sama — langsung publish (karena `published_at <= now()`).

**Manual posts** (dari editor) tidak terpengaruh slot AI. Berbeda tabel, berbeda slot.

---

## Scheduler

File: `routes/console.php`

```php
// Scheduler aktif (hanya 1):
Schedule::command('app:auto-pipeline --max=5')
    ->dailyAt('03:30')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/auto-pipeline.log'));
```

**HANYA `app:auto-pipeline`** yang jalan otomatis via scheduler. Publish scheduler (`app:publish-scheduled-posts`) sudah dihapus karena post langsung `active`.

---

## Yang Jalan Otomatis

| Komponen | Command | Keterangan |
|----------|---------|-----------|
| Scrape + Paraphrase | `php artisan queue:work` | WAJIB — proses jobs dari queue |
| Auto-pipeline | `php artisan schedule:run` (cron) | Jalan 03:30 WIB daily |
| Scheduler publish | — | TIDAK ADA — post langsung active |

### Server Production

```bash
# Cron untuk scheduler (1x auto-pipeline):
* * * * * php /path-to/artisan schedule:run >> /dev/null 2>&1

# Supervisor untuk queue worker:
php artisan queue:work redis --sleep=3 --tries=3
```

> **Catatan:** `schedule:work` (di local Herd) tidak diperlukan untuk publish. Cukup `queue:work` untuk proses scrape + paraphrase.

---

## Database Tables

### `ref_articles`
- Menyimpan article yang sudah di-approve dari hasil scraping
- `ai_research_status`: `idle` → `researching` → `done` / `failed`
- `generated_post_id`: link ke `posts` setelah paraphrase selesai
- `source_url`: URL asli dari SEJ/SEL
- `content`: di-NULL-kan setelah paraphrase selesai (hemat DB)

### `research_recommendations`
- Hasil scraping: URL + confidence score
- Status: `pending` → di-approve / di-reject
- Duplicate check: cek `ref_articles.source_url` saat scrape

### `posts`
- `status='active'`: post live di beranda
- `published_at`: jadwal muncul di beranda
- `published_by='system'`: buatan AI | `published_by=admin_id`: manual
- `source`: URL asli (AI posts) | `NULL`: manual posts

### `editor_preferences`
- Kata kunci AI untuk scraping
- `blocklist_urls`: URL yang di-reject
- `confidence`: score pembelajaran AI

---

## Admin Flow

```
Scraping (menu)
  └─ Research keyword → hasil di research_recommendations
  └─ Hasil Scraping (menu)
       └─ Approve → pindahkan ke ref_articles + AI learning
       └─ Reject → hapus + blocklist URL
  └─ Scraper Config (menu)

Ref Articles (menu)
  └─ Generate → paraphrase → save post (active)
  └─ Generate All Idle → batch max 5
  └─ Semua Ref Articles

Postingan AI (menu, ?source=ai)
  └─ post dengan published_by='system' atau source IS NOT NULL

Postingan Mandiri (menu, ?source=manual)
  └─ post dengan published_by!='system' dan source IS NULL
```

---

## Status Badge di Admin

| Badge | Kondisi |
|-------|---------|
| 🟢 **Published** | `status='active'` + `published_at <= now()` |
| 🔵 **Terjadwal** | `status='active'` + `published_at > now()` |
| 🟡 **Draft** | `status='inactive'` |
| 🔵 **AI** (kolom Asal) | `published_by='system'` |
| ⚪ **Editor** (kolom Asal) | `published_by!=system` |

---

## Files Utama

| File | Fungsi |
|------|--------|
| `routes/console.php` | Scheduler definitions |
| `app/Console/Commands/AutoPipeline.php` | Orchestrator: research + dispatch scrape jobs |
| `app/Jobs/KeywordResearchJob.php` | Scrape sitemap SEJ + SEL |
| `app/Jobs/ScrapeParaphraseJob.php` | Scrape + DeepSeek paraphrase + save (via auto-pipeline) |
| `app/Jobs/GenerateFromRefArticleJob.php` | Generate dari ref_articles (via admin manual) |
| `app/Services/SitemapScraperService.php` | Sitemap discovery + URL extraction |
| `app/Services/SearchEngineLandScraperService.php` | Article detail scraper |
| `app/Models/RefArticle.php` | Model ref_articles |
| `app/Models/ResearchRecommendation.php` | Model research_recommendations |
| `app/Http/Controllers/Admin/PostsController.php` | CRUD posts + filter AI/Mandiri |
| `app/Http/Controllers/Admin/ScrapingController.php` | Research + keyword management |
| `app/Http/Controllers/Admin/ScrapeResultController.php` | Approve/reject hasil scraping |
| `app/Http/Controllers/Admin/RefArticleController.php` | Generate dari ref_articles |
| `app/Http/Controllers/Admin/HomeController.php` | Dashboard AI pipeline stats |

---

## Commit History (Pipeline Refactoring)

| Commit | Deskripsi |
|--------|---------|
| `ade5953` | Post filtering: AI vs Mandiri separate pages |
| `e07513d` | Research stats fix + orphaned records cleanup |
| `55c2216` | HomeController: ai_research_status column fix |
| `1cb7e86` | Duplicate prevention + scheduler + DeepSeek robustness |
| `cd2987a` | Update comment: 08:00 → 03:30 WIB |
| `b680bce` | Fix DeepSeek \n newline: normalize content |
| `931cd92` | Status badge: add Terjadwal state |
| `f413f2c` | Edit post: add status info box |
| `d050bfb` | Post langsung active: hapus scheduler publish |

---

## Catatan Teknis

### DeepSeek Parsing
- Try direct `json_decode` first
- Fallback: markdown code block extraction
- Fallback: `{...}` block extraction
- Normalize: `preg_replace('/\\\\n|\\\\r|\r\n|\r|\n/', ' ', $content)`
- HTML tags (`<p>`, `<h2>`, `<strong>`) sudah define structure

### Confidence Scoring
- Base score: 30
- +70: keyword AI di URL slug
- +5: AI entity match
- +3: domain SEJ/SEL
- -10 to -20: bad patterns (review, comparison, brand HP, wordle, nyt)
- **Threshold: 45%** — di bawah ini tidak disimpan

### Blocklist
- Di simpan di `editor_preferences.blocklist_urls` (JSON array)
- Dicek saat scraping: skip jika URL ada di blocklist
- Clear via "Clear All Blocklists" button di admin

### Daily Limit
- Default: **5 artikel/hari** dari `ScraperConfig::getDailyLimit()`
- Bisa diubah via Scraping menu → Scraper Config
