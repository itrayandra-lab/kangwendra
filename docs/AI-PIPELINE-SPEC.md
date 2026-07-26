# Kangwendra AI Auto-Feed Pipeline Redesign

**Versi:** 1.0
**Tanggal:** 26 Juli 2026
**Status:** Ready for Implementation

---

## 1. Overview

### 1.1 Objectives

- Mengganti sistem scraping lama (Yahoo Tech + Pharma) dengan sistem baru berbasis **Search Engine Land + Search Engine Journal**
- Memecah proses besar menjadi **job-job kecil** yang ringan dan bisa di-retry independent
- Mengurangi beban database dengan **cleanup konten asli** setelah paraphrase selesai
- Membangun **AI learning system** yang bisa belajar dari pilihan (untuk fase future)
- Sistem berjalan **otomatis tanpa campur tangan editor** (fully automated backend)
- Batas **5 artikel/hari** dengan publish terjadwal

### 1.2 Daily Automated Flow

```
08:00 WIB (SETIAP PAGI) — AUTOMATED
│
├─ KeywordResearchJob
│   └─ AI research keyword berdasarkan preferensi editor
│
├─ AI auto-select URL (confidence >= 85%)
│   └─ Max 5 URL
│
├─ ScrapeParaphraseJob × 5 (per URL = 1 job independent)
│   ├─ HTTP GET → Parse → Validate → Save RefArticle
│   ├─ DeepSeek Paraphrase → Save Post
│   └─ Cleanup: Hapus original_content (hemat DB)
│
├─ Schedule publish:
│   ├─ Post #1 → hari ini 08:00 WIB
│   ├─ Post #2 → hari ini 13:00 WIB
│   ├─ Post #3 → hari ini 16:00 WIB
│   ├─ Post #4 → besok 08:00 WIB
│   └─ Post #5 → besok 13:00 WIB
│
├─ Auto-publish di jam 08:00, 13:00, 16:00 WIB (scheduler existing)
│
└─ AI Learning (background):
    ├─ Kalau source URL mati (404/timeout) → SKIP, jangan simpan
    └─ Track preference score (untuk future use)
```

---

## 2. Database Schema

### 2.1 Table: `editor_preferences` (BARU)

```php
Schema::create('editor_preferences', function (Blueprint $table) {
    $table->id();
    $table->string('keyword', 255)->unique();
    $table->string('topic', 255)->nullable();
    $table->integer('approved_count')->default(0);
    $table->integer('rejected_count')->default(0);
    $table->integer('unpublished_count')->default(0);
    $table->decimal('score', 8, 4)->default(0);
    $table->decimal('confidence', 8, 4)->default(50);
    $table->text('blocklist_urls')->nullable();
    $table->text('blocklist_patterns')->nullable();
    $table->timestamps();
    $table->index('keyword');
    $table->index('score');
    $table->index('confidence');
});
```

### 2.2 Table: `research_recommendations` (BARU)

```php
Schema::create('research_recommendations', function (Blueprint $table) {
    $table->id();
    $table->string('keyword', 255);
    $table->string('url', 500);
    $table->string('title', 500)->nullable();
    $table->string('domain', 255)->nullable();
    $table->string('snippet', 1000)->nullable();
    $table->decimal('confidence_score', 8, 4)->nullable();
    $table->enum('status', ['pending', 'approved', 'rejected', 'scraped'])->default('pending');
    $table->string('ref_article_id')->nullable();
    $table->timestamps();
    $table->index('keyword');
    $table->index('status');
    $table->unique('url');
});
```

### 2.3 Table: `ref_articles` (MODIFIKASI)

```php
// KOLOM BARU:
$table->enum('ai_research_status', ['idle', 'researching', 'done', 'failed'])
    ->default('idle')->after('ai_status');
$table->string('source_keyword', 255)->nullable()->after('source_domain');
$table->text('research_notes')->nullable()->after('ai_research_status');

// SETELAH PARAPHRASE SELESAI:
// - content → NULL (hapus konten asli, hemat DB)
// - original_content_hash → NULL
```

### 2.4 Table: `posts` (MODIFIKASI)

```php
// KOLOM BARU:
$table->string('published_by', 50)->default('system')->after('status');
$table->timestamp('unpublished_at')->nullable()->after('published_at');
$table->string('unpublished_reason', 255)->nullable()->after('unpublished_at');
```

---

## 3. Jobs

### 3.1 KeywordResearchJob
AI research keyword → output 5 recommended URLs dari SEL/SEJ.

### 3.2 ScrapeParaphraseJob
1 job = scrape + paraphrase + save + cleanup.
- Kalau source URL mati (404/timeout): SKIP, jangan simpan
- Validate: title, content min 200 chars, image required
- After done: content = NULL (hemat DB)

### 3.3 UpdateEditorPreferenceJob
Background job untuk recalculate preference scores.

---

## 4. Daily Limit & Publish Slots

| Slot | Waktu | Keterangan |
|------|-------|-----------|
| #1 | 08:00 WIB | Post scraped pertama |
| #2 | 13:00 WIB | Post scraped kedua |
| #3 | 16:00 WIB | Post scraped ketiga |
| #4 | Besok 08:00 WIB | Post scraped keempat (overflow) |
| #5 | Besok 13:00 WIB | Post scraped kelima (overflow) |

- **Confidence threshold:** 85%
- **Source:** searchengineland.com, searchenginejournal.com
- **Daily limit:** 5 artikel

---

## 5. AI Learning

- Editor approve URL → score + confidence naik
- Editor reject URL → blocklist + score turun
- Post di-unpublish → confidence turun
- Confidence < 85% → tidak di-scrape

---

## 6. Files

### CREATE (11 files)
- `app/Jobs/KeywordResearchJob.php`
- `app/Jobs/ScrapeParaphraseJob.php`
- `app/Jobs/UpdateEditorPreferenceJob.php`
- `app/Console/Commands/AutoPipelineCommand.php`
- `app/Services/SearchEngineLandScraperService.php`
- `app/Services/KeywordResearchService.php`
- `app/Services/EditorPreferenceService.php`
- `app/Models/EditorPreference.php`
- `app/Models/ResearchRecommendation.php`
- Migration: `editor_preferences`
- Migration: `research_recommendations`

### MODIFY (6 files)
- `app/Models/RefArticle.php`
- `app/Models/Post.php`
- `app/Http/Controllers/Admin/RefArticleController.php`
- `app/Http/Controllers/Admin/PostController.php`
- `resources/views/pages/admin/ref-articles/index.blade.php`
- `resources/views/pages/admin/home/index.blade.php`
- `routes/console.php`

### DELETE (5 files)
- `app/Services/YahooTechScraperService.php`
- `app/Services/TechPharmaScraperService.php`
- `app/Jobs/GenerateAiArticleJob.php`
- `app/Console/Commands/ProcessPendingAi.php`
- `app/Console/Commands/AutoFeedCommand.php`

---

## 7. Implementation Order

1. Database migrations
2. Models (EditorPreference, ResearchRecommendation) + modify RefArticle, Post
3. Services (SearchEngineLandScraper, KeywordResearch, EditorPreference)
4. Jobs (KeywordResearchJob, ScrapeParaphraseJob, UpdateEditorPreferenceJob)
5. AutoPipelineCommand + PostController (unpublish, regenerate)
6. RefArticleController + routes
7. Views (ref-articles/index, posts/index, dashboard)
8. Cleanup old files
9. Routes & console schedule updates
10. Testing & verification
