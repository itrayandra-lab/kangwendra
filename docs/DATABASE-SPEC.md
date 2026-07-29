# Database Schema — Kangwendra

**Versi:** 1.0
**Tanggal:** 29 Juli 2026
**Ringkasan:** Schema database terkait article pipeline, web publishing, dan AI automation.

---

## 1. Schema Tables

### 1.1 `posts` — Artikel yang Dipublikasikan

Tabel utama untuk semua artikel (AI dan manual).

```sql
CREATE TABLE posts (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug               VARCHAR(255) UNIQUE,       -- URL slug, unik
    title              VARCHAR(255),               -- Judul artikel
    image              TEXT NULL,                  -- Path gambar featured
    content            LONGTEXT NULL,             -- Konten HTML artikel
    counter            INT DEFAULT 0,              -- Jumlah views/pembaca
    status             VARCHAR(255),               -- 'active' atau 'inactive'
    published_by       VARCHAR(50) DEFAULT 'system', -- 'system' = AI | user_id = manual
    published_at       TIMESTAMP NULL,              -- Jadwal publish (datang muncul di beranda)
    unpublished_at     TIMESTAMP NULL,             -- Waktu di-unpublish
    unpublished_reason VARCHAR(255) NULL,         -- Alasan unpublish
    category_id        BIGINT UNSIGNED NULL,       -- FK ke post_categories.id
    tags               JSON NULL,                  -- Array tag names (JSON array)
    created_by         BIGINT UNSIGNED,            -- FK ke users.id (pembuat)
    updated_by         BIGINT UNSIGNED NULL,      -- FK ke users.id (editor terakhir)
    source             TEXT NULL,                 -- URL artikel asli (AI posts only)
    domain             VARCHAR(255) NULL,         -- Domain sumber asli (AI posts only)
    meta_data          JSON NULL,                  -- Data SEO + AI metadata (JSON object)
    created_at         TIMESTAMP,
    updated_at         TIMESTAMP,

    FOREIGN KEY (category_id) REFERENCES post_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES post_categories(id)
);
```

**Kolom Penting:**

| Kolom | Deskripsi | Contoh |
|-------|---------|--------|
| `status` | `active` = live, `inactive` = draft | `active` |
| `published_by` | `system` = AI, user_id = manual | `system` |
| `published_at` | Kapan muncul di beranda | `2026-07-29 08:00:00` |
| `source` | URL asli dari SEJ/SEL (AI only) | `https://searchengineland.com/...` |
| `tags` | JSON array tag names | `["AI","Teknologi","Google"]` |
| `meta_data` | SEO + AI metadata (JSON) | `{seo_title, seo_desc, ref_article_id, ...}` |
| `counter` | View count | `0`, `1523`, dst |

**Konvensi Filtering AI vs Manual:**

| Tipe | published_by | source |
|------|-------------|--------|
| **AI** | `'system'` | URL SEJ/SEL (tidak null) |
| **Manual** | user_id | `NULL` |

**Konvensi Status Badge di Admin:**

| Badge | status | published_at | Keterangan |
|-------|--------|-------------|-----------|
| 🟢 Published | `active` | `<= now()` | Sudah live |
| 🔵 Terjadwal | `active` | `> now()` | Belum publish |
| 🟡 Draft | `inactive` | - | Draft |

---

### 1.2 `ref_articles` — Referensi Artikel Hasil Scraping

Tabel untuk menyimpan hasil scraping sebelum dip paraphrase menjadi artikel baru.

```sql
CREATE TABLE ref_articles (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_url         VARCHAR(255) UNIQUE,        -- URL artikel asli dari SEJ/SEL
    source_domain       VARCHAR(255),               -- Domain: searchengineland.com / searchenginejournal.com
    title               VARCHAR(255),               -- Judul artikel asli
    content            LONGTEXT NULL,              -- Isi artikel asli (di-NULL-kan setelah paraphrase)
    image_url          TEXT NULL,                  -- URL gambar artikel asli
    tags               JSON NULL,                  -- Tags dari artikel asli
    author             VARCHAR(255) NULL,         -- Penulis asli
    published_at       TIMESTAMP NULL,              -- Tanggal publikasi artikel asli
    ai_status          ENUM('pending','processing','done','failed')
                          DEFAULT 'pending',       -- DEPRECATED, pakai ai_research_status
    ai_research_status ENUM('idle','researching','done','failed')
                          DEFAULT 'idle',          -- Status pipeline AI
    source_keyword     VARCHAR(255) NULL,          -- Keyword yang dipakai saat scraping
    research_notes     TEXT NULL,                  -- Catatan proses research
    moved_from_scrape  BOOLEAN DEFAULT FALSE,     -- Dari auto-pipeline atau manual approve
    batch_id           VARCHAR(255) NULL,          -- Batch ID untuk grouping
    ai_error           TEXT NULL,                 -- Pesan error jika failed
    generated_post_id  BIGINT UNSIGNED NULL,       -- FK ke posts.id (artikel hasil generate)
    created_at         TIMESTAMP,
    updated_at         TIMESTAMP
);
```

**Pipeline Status (`ai_research_status`):**

| Status | Arti |
|--------|------|
| `idle` | Belum diproses, siap di-generate |
| `researching` | Sedang diproses (sedang paraphrase) |
| `done` | Sudah generate + post sudah dibuat |
| `failed` | Gagal, ada error |

**Flow Data:**

```
scrape (SearchEngineLandScraperService)
  → ref_articles (source_url, title, content, image_url, author, published_at)
  → approve (ScrapeResultController::approve)
  → ref_articles (ai_research_status=idle)
  → generate (RefArticleController / GenerateFromRefArticleJob)
  → paraphrase (DeepSeek API)
  → posts (artikel baru Bahasa Indonesia)
  → ref_articles.generated_post_id = posts.id
  → ref_articles.content = NULL (hemat DB)
```

---

### 1.3 `research_recommendations` — URL Hasil Sitemap Scraping

Tabel sementara untuk URL yang ditemukan dari sitemap sebelum di-approve.

```sql
CREATE TABLE ref_articles (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    keyword             VARCHAR(255),               -- Keyword scraping
    url                VARCHAR(500),              -- URL artikel
    title              VARCHAR(500) NULL,         -- Judul (dari URL slug extraction)
    domain             VARCHAR(255) NULL,         -- Domain
    snippet            VARCHAR(1000) NULL,        -- Snippet deskripsi
    confidence_score   DECIMAL(8,4) NULL,         -- Score 0-100
    status             ENUM('pending','approved','rejected','scraped')
                          DEFAULT 'pending',
    ref_article_id     VARCHAR(255) NULL,         -- ID ref_article (setelah approve)
    created_at         TIMESTAMP,
    updated_at         TIMESTAMP,

    UNIQUE INDEX (keyword, url)
);
```

**Status:**

| Status | Arti |
|--------|------|
| `pending` | Belum di-approve/reject |
| `approved` | Sudah di-approve → dipindahkan ke ref_articles |
| `rejected` | Di-reject → dihapus |
| `scraped` | Sudah di-scrape via auto-pipeline |

**Confidence Scoring:**

```
Base score: 30
+70  → AI keyword di URL slug
+5   → AI entity match
+3   → Domain SEJ/SEL
-10 to -20 → Bad patterns (review, comparison, HP brand, wordle, nyt)
≥45  → Disimpan ke research_recommendations
<45  → Di-skip saat scraping
```

---

### 1.4 `editor_preferences` — Keyword & AI Learning

Tabel untuk keyword scraping dan feedback learning.

```sql
CREATE TABLE editor_preferences (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    keyword             VARCHAR(255) UNIQUE,        -- Kata kunci scraping
    topic              VARCHAR(255) NULL,         -- Topik label (AI & Teknologi, dll)
    approved_count      INT DEFAULT 0,             -- Jumlah URL yang di-approve
    rejected_count     INT DEFAULT 0,              -- Jumlah URL yang di-reject
    unpublished_count  INT DEFAULT 0,              -- Jumlah post yang di-unpublish
    score              DECIMAL(8,4) DEFAULT 0,   -- Score kumulatif
    confidence         DECIMAL(8,4) DEFAULT 50,  -- Confidence 0-100
    blocklist_urls     TEXT NULL,                  -- JSON array URL yang di-block
    blocklist_patterns TEXT NULL,                  -- JSON array pattern yang di-block
    created_at         TIMESTAMP,
    updated_at         TIMESTAMP,

    INDEX (keyword),
    INDEX (score),
    INDEX (confidence)
);
```

**AI Learning Feedback:**

| Action | Effect |
|--------|--------|
| URL approved | `approved_count++`, `confidence += 5` |
| URL rejected | `rejected_count++`, `confidence -= 3` |
| Post unpublished | `unpublished_count++`, `confidence -= 5` |
| Confidence < 45 | Keyword di-skip saat scraping |

---

### 1.5 `scraper_configs` — Konfigurasi Scraper

Key-value config untuk pipeline scraper.

```sql
CREATE TABLE scraper_configs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key         VARCHAR(255) UNIQUE,   -- 'keywords', 'source_urls', 'daily_limit', dll
    value       TEXT,                  -- Nilai (JSON string untuk array)
    type        VARCHAR(255) DEFAULT 'string',  -- 'string', 'array', 'integer'
    label       VARCHAR(255),           -- Label human-readable
    description TEXT NULL,             -- Penjelasan
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Config Keys yang Dipakai:**

| key | type | default | Deskripsi |
|-----|------|---------|-----------|
| `keywords` | array | 12 AI keywords | Keyword untuk sitemap scraping |
| `source_urls` | array | SEJ + SEL sitemap | URL sitemap untuk scrape |
| `daily_limit` | integer | 5 | Maksimal artikel/hari |
| `confidence_threshold` | integer | 45 | Minimal confidence untuk disimpan |
| `min_year` | integer | 2022 | Skip artikel sebelum tahun ini |

---

### 1.6 `publish_schedules` — Jadwal Publish

> ⚠️ **CATATAN:** Tabel ini TIDAK dipakai lagi oleh pipeline AI.
> Post AI langsung `status='active'` saat dibuat.
> Tabel ini hanya untuk referensi dan jadwal manual editor.
> Default: `08:00`, `13:00`, `16:00` WIB.

```sql
CREATE TABLE publish_schedules (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    time        TIME,                              -- Jam publish (08:00:00, 13:00:00, 16:00:00)
    day_of_week TINYINT NULL,                     -- 0=Sunday to 6=Saturday, NULL=setiap hari
    is_active   BOOLEAN DEFAULT TRUE,              -- Aktif atau tidak
    max_posts   TINYINT DEFAULT 1,                -- Maksimal post per jadwal
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

### 1.7 `share_domains` — n8n Webhook Distribution

Domain yang menerima post via n8n webhook automation.

```sql
CREATE TABLE share_domains (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    domain_name VARCHAR(255),              -- Nama domain, misal: technohp.com
    webhook_url VARCHAR(255),              -- URL webhook n8n
    api_key     VARCHAR(64) NULL UNIQUE,   -- API key untuk autentikasi
    status     VARCHAR(255) DEFAULT 'inactive',  -- 'active' atau 'inactive'
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Distribusi Post:**
- Admin memilih domain saat create/edit post
- `DistributePostJob` dispatch ke n8n webhook
- n8n otomatis publish ke WordPress/domain tujuan

---

### 1.8 `post_categories` — Kategori Artikel

```sql
CREATE TABLE post_categories (
    id      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug    VARCHAR(255) UNIQUE,  -- URL slug kategori, misal: 'teknologi'
    name    VARCHAR(255),        -- Nama kategori, misal: 'Teknologi'
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

### 1.9 `post_tags` — Tags Artikel

```sql
CREATE TABLE post_tags (
    id      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug    VARCHAR(255) UNIQUE,  -- URL slug tag, misal: 'ai-teknologi'
    name    VARCHAR(255),        -- Nama tag, misal: 'AI & Teknologi'
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

### 1.10 `web_identities` — Identitas Website Utama

Konfigurasi meta untuk SEO dan branding.

```sql
CREATE TABLE web_identities (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    web_name         VARCHAR(255) NULL,    -- Nama website
    email            VARCHAR(255) NULL UNIQUE,
    domain           VARCHAR(255) NULL,    -- Domain utama
    phone_number     VARCHAR(255) NULL,
    facebook_link    VARCHAR(255) NULL,
    instagram_link   VARCHAR(255) NULL,
    youtube_link     VARCHAR(255) NULL,
    twitter_link     VARCHAR(255) NULL,
    meta_title       VARCHAR(255) NULL,    -- SEO title
    meta_description TEXT NULL,            -- SEO description
    meta_keywords    TEXT NULL,            -- SEO keywords
    og_image         VARCHAR(255) NULL,    -- Open Graph image
    google_maps      VARCHAR(255) NULL,
    favicon          VARCHAR(255) NULL,
    logo             VARCHAR(255) NULL,
    status           ENUM('active','inactive') DEFAULT 'active',
    version          VARCHAR(255) NULL,
    is_master        BOOLEAN DEFAULT FALSE,  -- TRUE = identitas utama
    api_key          VARCHAR(255) NULL,    -- API key untuk distribusi
    created_at       TIMESTAMP,
    updated_at       TIMESTAMP
);
```

---

## 2. Alur Data Pipeline

```
┌─────────────────────────────────────────────────────────┐
│  SITEMAP SCRAPING (KeywordResearchJob)                    │
│  SitemapScraperService → findUrls(keyword)              │
│  Confidence scoring (≥45%)                            │
│  Skip: ref_articles.source_url + blocklist             │
└──────────────────┬──────────────────────────────────────┘
                   │
                   ▼
        research_recommendations
        (status=pending, confidence_score)
                   │
                   │ admin approve
                   ▼
        ref_articles
        (ai_research_status=idle)
                   │
                   │ admin generate / auto-generate
                   ▼
        DeepSeek paraphrase
        (artikel baru Bahasa Indonesia)
                   │
                   ▼
        posts (status=active, published_at=slot)
        source=URL_SEJ_SEL, published_by=system
        tags=[...], meta_data={...}
                   │
                   ├─► beranda (published_at <= now) ✅
                   ├─► share_domains (DistributePostJob → n8n) ✅
                   └─► ref_articles.generated_post_id = posts.id ✅
```

---

## 3. Filter AI vs Manual Posts

Di admin dan query public, post dipisahkan berdasarkan:

```sql
-- POSTINGAN AI (?source=ai)
WHERE published_by = 'system' OR source IS NOT NULL

-- POSTINGAN MANDIRI (?source=manual)
WHERE published_by != 'system' AND (source IS NULL OR source = '')
```

Di database:

| Field | AI Posts | Manual Posts |
|-------|---------|-------------|
| `published_by` | `'system'` | `1`, `2`, dll (user_id) |
| `source` | `https://searchengineland.com/...` | `NULL` |
| `domain` | `searchengineland.com` / `searchenginejournal.com` | `NULL` |
| `meta_data->ref_article_id` | Ada (link ke ref_articles) | `NULL` |

---

## 4. Publish Slot System

Post AI langsung `status='active'` dengan `published_at` sesuai slot.

```php
$slots = [
    0 => ['hour' => 8,  'label' => 'pagi'],
    1 => ['hour' => 13, 'label' => 'siang'],
    2 => ['hour' => 16, 'label' => 'sore'],
];

// Cari slot kosong hari ini
// Kalau penuh → overflow ke 08:00 HARI INI (langsung publish)
```

**Contoh:**
- Scraping jam 03:30, dapat 5 berita
- Post #1 → published_at = today 08:00 → publish jam 08:00
- Post #2 → published_at = today 13:00 → publish jam 13:00
- Post #3 → published_at = today 16:00 → publish jam 16:00
- Post #4 → published_at = today 08:00 → publish SEKARANG (overflow)
- Post #5 → published_at = today 08:00 → publish SEKARANG (overflow)

**Beranda query:**
```php
Posts::where('status', 'active')
    ->where('published_at', '<=', Carbon::now())
    ->get();
```
Post langsung muncul saat `published_at` tercapai. Tidak butuh scheduler.

---

## 5. Meta Data AI Post

JSON field `meta_data` di tabel `posts` untuk AI posts:

```json
{
    "seo_title": "Judul artikel AI",
    "seo_desc": "Meta description dari DeepSeek",
    "excerpt": "Ringkasan 2-3 kalimat",
    "ref_article_id": 15,
    "ref_source_url": "https://searchengineland.com/...",
    "ref_title": "Judul artikel asli",
    "ai_model": "deepseek-v4-pro",
    "publish_slot": "slot_0_pagi",
    "batch_id": "uuid-string"
}
```

---

## 6. Indexes & Performance

| Tabel | Index | Untuk |
|-------|-------|-------|
| `posts` | `slug` (unique) | Single post lookup |
| `posts` | `status + published_at` | Beranda query |
| `posts` | `published_by` | Filter AI/Mandiri |
| `posts` | `source` | Filter AI/Mandiri |
| `ref_articles` | `source_url` (unique) | Duplicate prevention |
| `ref_articles` | `ai_research_status` | Dashboard stats |
| `ref_articles` | `source_keyword` | Filter by keyword |
| `research_recommendations` | `keyword + url` (unique) | Duplicate prevention |
| `research_recommendations` | `keyword` | Filter by keyword |
| `research_recommendations` | `status` | Filter pending/approved |
| `editor_preferences` | `keyword` (unique) | Lookup |
| `editor_preferences` | `confidence` | Top keyword selection |
