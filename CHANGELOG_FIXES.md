# CHANGELOG FIXES — Kangwendra

Dokumentasi bug fix & regression prevention. Setiap entri ditulis setelah fix terverifikasi.

---

### Fix #7 — Scraping Pipeline UX Overhaul: Batch Progress + 5 Keyword + Keyword Alternatif

**Tanggal:** 2026-08-04

| Field | Detail |
|-------|--------|
| **Gejala** | (1) Research All dispatch 39 keyword → 0 hasil. (2) Tidak ada pemberitahuan progress. (3) Hasil kosong tanpa alasan. (4) Pagination 20 per page. |
| **Akar** | (1) `researchAll()` dispatch semua 39 keyword. (2) Tidak ada batch tracking. (3) Empty state tanpa suggest alternatif. (4) Pagination 20. |
| **File** | `ScrapingController.php`, `ScrapeResultController.php`, `routes/web.php`, `batch-progress.blade.php`, `scrape-results/index.blade.php`, `KeywordResearchJob.php` |
| **Fix** | (1) research(): dispatch + batch_id → batch-progress page. (2) researchAll(): 5 keyword approved_count tertinggi + batch_id. (3) batchProgress(): keyword list + progress bar + auto-refresh 5 detik. (4) Pagination 5/page. (5) Empty state + keyword alternatif yang bisa diklik. (6) Batch progress link di hasil scraping. |
| **Verifikasi** | Klik Research All → batch progress page → auto-refresh → setiap keyword selesai → hasil muncul → "Lihat Hasil Scraping". |
| **Pelajaran** | UX research pipeline: batch tracking + progress + suggestion saat 0 hasil + batasan max per batch. |
| **Keyword Log** | batch progress, 5 keyword, approved_count, auto-refresh, keyword alternatif |
| **Deploy** | Local dev |

---

### Fix #6 — maxResponseSize Not Found (GenerateAll FAIL)

**Tanggal:** 2026-08-04

| Field | Detail |
|-------|--------|
| **Gejala** | Klik Generate → semua job `FAIL` dalam 20-95ms. Log: `Method Illuminate\Http\Client\PendingRequest::maxResponseSize does not exist.` |
| **Akar** | Semua `->maxResponseSize(N)` yang ditambahkan untuk batasi response size tidak tersedia di versi Laravel yang terinstall di Herd. |
| **File** | `app/Services/SitemapScraperService.php` (4x), `app/Services/SearchEngineLandScraperService.php` (2x) |
| **Fix** | Hapus semua `->maxResponseSize()`. Memory limit `512M` sudah cukup untuk handle large responses. |
| **Verifikasi** | Queue worker restart. Jobs jalan tanpa error maxResponseSize. |
| **Pelajaran** | Selalu cek apakah fitur yang digunakan tersedia di versi library yang terinstall. |
| **Keyword Log** | maxResponseSize, method not exist, PendingRequest |
| **Deploy** | Local dev |

---

### Fix #7 — Scraping Pipeline UX Overhaul: Batch Progress + 5 Keyword + Keyword Alternatif

**Tanggal:** 2026-08-04

| Field | Detail |
|-------|--------|
| **Gejala** | (1) Research All dispatch 39 keyword sekaligus → 0 hasil. (2) Tidak ada pemberitahuan progress. (3) Hasil kosong tanpa alasan. (4) Pagination 20 per page. |
| **Akar** | (1) `researchAll()` dispatch semua 39 keyword. (2) Tidak ada batch progress tracking. (3) Empty state tanpa suggest keyword alternatif. (4) Pagination 20. |
| **File** | `ScrapingController.php` (rewrite research+researchAll+batch methods), `ScrapeResultController.php` (pagination 5 + altKeywords), `routes/web.php` (2 route baru), `batch-progress.blade.php` (view baru), `scrape-results/index.blade.php` (flash+alt+progress link), `KeywordResearchJob.php` (batch_id param) |
| **Fix** | (1) research(): dispatch dengan batch_id, redirect ke batch-progress page. (2) researchAll(): ambil 5 keyword dengan approved_count tertinggi, dispatch dengan batch_id sama. (3) batchProgress(): view dengan keyword list + progress bar + auto-refresh 5 detik. (4) batchStatus(): API endpoint JSON untuk polling. (5) Pagination 5 per page. (6) Empty state dengan keyword alternatif yang bisa diklik langsung. (7) Batch progress link di hasil scraping page. |
| **Verifikasi** | Klik Research All → redirect ke batch progress page → auto-refresh → setiap keyword selesai → hasil muncul → klik "Lihat Hasil Scraping". |
| **Pelajaran** | UX research pipeline butuh: batch tracking + progress visibility + suggestion saat 0 hasil + batasan max per batch. |
| **Keyword Log** | batch progress, 5 keyword, approved_count, auto-refresh, keyword alternatif |
| **Deploy** | Local dev |



**Tanggal:** 2026-08-03

| Field | Detail |
|-------|--------|
| **Gejala** | Klik "Research" keyword (misal: "gemini") → `504 Gateway Time-out nginx/1.25.2` |
| **Akar** | `ScrapingController::research()` menjalankan `KeywordResearchJob::handle()` secara **synchronously**. Sitemap scraping butuh 1-5 menit tapi nginx timeout cuma ~60 detik. |
| **File** | `app/Http/Controllers/Admin/ScrapingController.php` |
| **Fix** | Ubah `research()` dari sync ke **async dispatch** (`KeywordResearchJob::dispatch()`). Cleanup tetap sync. |
| **Verifikasi** | Klik Research → message "sedang diproses di background". Hasil URL muncul setelah 1-5 menit. |
| **Pelajaran** | Setiap operasi >30 detik HARUS jalan via queue, tidak pernah synchronously dalam HTTP request. |
| **Keyword Log** | 504 gateway timeout, nginx, sitemap timeout, sync research |
| **Deploy** | Local dev |

---

### Fix #4 — Daily Limit Enforcement: 39 Artikel vs 5 Limit

**Tanggal:** 2026-08-03

| Field | Detail |
|-------|--------|
| **Gejala** | `daily_limit = 5` tapi AutoPipeline menghasilkan 39 artikel. |
| **Akar** | (1) `AutoPipeline` hitung `Posts::where('status','draft')` (salah). (2) `ScrapeParaphraseJob` tidak ada daily limit check. (3) `GenerateFromRefArticleJob` tidak ada daily limit check. |
| **File** | `ScrapeParaphraseJob.php`, `GenerateFromRefArticleJob.php`, `AutoPipeline.php` |
| **Fix** | Daily limit guard di TOP setiap job `handle()`. AutoPipeline hitung dari `RefArticle::ai_research_status='done'` today. DB: flush 9 failed jobs + truncate 30 pending jobs. |
| **Verifikasi** | `php artisan queue:work`. Max 5 article/hari enforced di semua entry point. |
| **Pelajaran** | Daily limit harus di-enforce di 3 level: dispatch, queue, dan guard job handle. |
| **Keyword Log** | daily_limit, 39 articles, limit enforcement, abort job |
| **Deploy** | Local dev |

---

### Fix #3 — Approve 0 URLs: Semua Rec Sudah Punya RefArticle (Orphan Recs)

**Tanggal:** 2026-08-03

| Field | Detail |
|-------|--------|
| **Gejala** | "0 URL di-approve dan dipindahkan ke Ref Articles. 20 dilewati." |
| **Akar** | Auto-pipeline bikin `RefArticle` langsung tanpa hapus `ResearchRecommendation`. Rec orphan menumpuk. |
| **File** | `ScrapeResultController.php` (approve), `ScrapingController.php` (research), `RefArticleController.php` (guards) |
| **Fix** | (1) approve(): URL sudah ada RefArticle → hapus Rec + info. (2) research(): auto-delete orphan Recs. (3) DB cleanup 20 stuck Recs. |
| **Verifikasi** | ResearchRecommendation sekarang 0. Tidak ada orphan Recs. |
| **Pelajaran** | Dual pipeline butuh cleanup sync antara Rec dan RefArticle. |
| **Keyword Log** | orphan recs, already moved, skip approve, RefArticle exists |
| **Deploy** | Local dev |

---

### Fix #2 — Generate All Stuck: 10 → 3 → 0 (Hardcoded Limit)

**Tanggal:** 2026-08-03

| Field | Detail |
|-------|--------|
| **Gejala** | Klik "Generate All Idle" → cuma 5, klik lagi → 3, lagi → 0. |
| **Akar** | `generateAll()` punya `limit(5)` HARDCODED. Queue worker crashed → 30 jobs stuck. Duplicate route group. |
| **File** | `RefArticleController.php` (generateAll), `routes/web.php` (duplicate), `AutoPipeline.php` (draft→active) |
| **Fix** | Ganti `limit(5)` dengan dynamic cap dari `daily_limit - done_today`. Hapus duplicate route. Fix AutoPipeline. |
| **Verifikasi** | Generate All → semua idle articles ter-queue sesuai daily limit. |
| **Pelajaran** | Hardcoded number = bug. |
| **Keyword Log** | limit(5), hardcoded, generate all, idle, stuck |
| **Deploy** | Local dev |

---

### Fix #1 — Memory Exhaustion pada Queue Worker Saat Scraping

**Tanggal:** 2026-08-03

| Field | Detail |
|-------|--------|
| **Gejala** | `PHP Fatal error: Allowed memory size of 134217728 bytes exhausted` di Guzzle stream_get_contents. |
| **Akar** | PHP memory limit 128MB terlalu kecil untuk buffer entire HTTP response + regex processing. |
| **File** | `AppServiceProvider.php`, `KeywordResearchJob.php`, `ScrapeParaphraseJob.php`, `GenerateFromRefArticleJob.php`, `DistributePostJob.php` |
| **Fix** | `ini_set('memory_limit', '512M')` di `AppServiceProvider::boot()` + setiap job handle(). |
| **Verifikasi** | Queue worker jalan 1 jam tanpa OOM. |
| **Pelajaran** | Queue worker perlu memory limit eksplisit. Default 128MB terlalu kecil untuk scraping pipeline. |
| **Keyword Log** | memory_limit, OOM, Guzzle, allowed memory size |
| **Deploy** | Local dev |

