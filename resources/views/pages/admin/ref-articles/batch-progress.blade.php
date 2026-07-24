@extends('layouts.admin.app')
@section('title', $page)

@push('styles')
<style>
    .progress-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        text-align: center;
        flex: 1;
        min-width: 140px;
    }
    .progress-card .big-number {
        font-size: 52px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 6px;
    }
    .progress-card .label {
        font-size: 13px;
        opacity: 0.9;
    }
    .progress-ring-container {
        display: flex;
        justify-content: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .status-badge {
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }
    .badge-pending    { background: #fff3cd; color: #856404; }
    .badge-processing { background: #cfe2ff; color: #084298; }
    .badge-done       { background: #d1e7dd; color: #0f5132; }
    .badge-failed     { background: #f8d7da; color: #842029; }
    .error-item {
        background: #fff3f3;
        border-left: 3px solid #dc3545;
        padding: 10px 15px;
        margin-bottom: 8px;
        border-radius: 4px;
    }
    .steps-flow {
        display: flex;
        align-items: center;
        gap: 0;
        justify-content: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }
    .step-box {
        background: white;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 12px 20px;
        text-align: center;
        min-width: 120px;
    }
    .step-active { border-color: #28a745; background: #f0fff4; }
    .step-done   { border-color: #28a745; background: #d4edda; }
    .step-pending { border-color: #dee2e6; background: white; }
    .step-arrow { font-size: 18px; color: #adb5bd; padding: 0 6px; }
</style>
@endpush

@push('scripts')
<script>
let batchId = "{{ $batchId ?? '' }}";
let pollInterval = null;

function updateProgress() {
    if (!batchId) return;

    fetch("{{ route('ref-articles.batch-status') }}?batch_id=" + batchId)
        .then(r => r.json())
        .then(data => {
            if (data.error) return;

            // Update numbers
            let total = data.total || 0;
            let success = data.success || 0;
            let failed = data.failed || 0;
            let status = data.status || 'running';

            let done = success + failed;

            document.getElementById('total-num').textContent = total;
            document.getElementById('success-num').textContent = success;
            document.getElementById('failed-num').textContent = failed;
            document.getElementById('processing-num').textContent = total - done;
            document.getElementById('progress-bar').style.width = total > 0 ? Math.round((done / total) * 100) + '%' : '0%';
            document.getElementById('progress-pct').textContent = total > 0 ? Math.round((done / total) * 100) + '%' : '0%';

            // Update status badge
            let statusBadge = document.getElementById('batch-status-badge');
            if (status === 'complete') {
                statusBadge.innerHTML = '🎉 <strong>Selesai!</strong>';
                statusBadge.className = 'alert alert-success';
                stopPoll();
            } else {
                statusBadge.innerHTML = '🔄 Sedang diproses... (' + done + '/' + total + ')';
                statusBadge.className = 'alert alert-info';
            }
        })
        .catch(() => {});
}

function stopPoll() {
    if (pollInterval) clearInterval(pollInterval);
}

if (batchId) {
    updateProgress();
    pollInterval = setInterval(updateProgress, 3000);
}
</script>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">

        {{-- Steps Flow --}}
        <div class="steps-flow">
            <div class="step-box step-active">
                <div style="font-size:20px;">📥</div>
                <div style="font-weight:600;">1. Scrape</div>
                <div style="font-size:11px;color:#666;">Klik tombol Scrape</div>
            </div>
            <div class="step-arrow">→</div>
            <div class="step-box {{ ($processing + $pending) > 0 ? 'step-active' : 'step-done' }}">
                <div style="font-size:20px;">🤖</div>
                <div style="font-weight:600;">2. Generate AI</div>
                <div style="font-size:11px;color:#666;">
                    {{ ($processing + $pending) > 0 ? 'Sedang...' : 'Selesai' }}
                </div>
            </div>
            <div class="step-arrow">→</div>
            <div class="step-box {{ ($success + $failed) == $total && $total > 0 ? 'step-done' : 'step-pending' }}">
                <div style="font-size:20px;">📰</div>
                <div style="font-weight:600;">3. Publish</div>
                <div style="font-size:11px;color:#666;">Otomatis 08/13/16 WIB</div>
            </div>
        </div>

        {{-- Progress Cards --}}
        <div class="progress-ring-container">
            <div class="progress-card">
                <div class="big-number" id="total-num">{{ $total }}</div>
                <div class="label">📋 Total</div>
            </div>
            <div class="progress-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="big-number" id="success-num">{{ $success }}</div>
                <div class="label">✅ Berhasil</div>
            </div>
            <div class="progress-card" style="background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);">
                <div class="big-number" id="failed-num">{{ $failed }}</div>
                <div class="label">❌ Gagal</div>
            </div>
            <div class="progress-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="big-number" id="processing-num">{{ $processing + $pending }}</div>
                <div class="label">⏳ Proses...</div>
            </div>
        </div>

        {{-- Progress Bar --}}
        @if($total > 0)
        <div style="background:#e9ecef; border-radius:10px; height:28px; overflow:hidden; margin-bottom:20px;">
            <div id="progress-bar" style="display:inline-block; height:100%; background:#28a745; width:{{ $total > 0 ? round((($success + $failed) / $total) * 100) : 0 }}%; transition: width 0.5s;">
                <span id="progress-pct" style="display:inline-flex; align-items:center; justify-content:center; height:100%; color:white; font-size:13px; font-weight:600; padding:0 10px;">
                    {{ $total > 0 ? round((($success + $failed) / $total) * 100) : 0 }}%
                </span>
            </div>
        </div>
        @endif

        {{-- Status Notice --}}
        <div id="batch-status-badge" class="alert alert-info" style="text-align:center; margin-bottom:20px;">
            🔄 Sedang diproses... ({{ ($success + $failed) }}/{{ $total }})
        </div>

        {{-- Completion Message --}}
        @if(($success + $failed) == $total && $total > 0)
        <div class="alert alert-success" style="text-align:center; font-size:16px; padding:20px; margin-bottom:20px;">
            🎉 <strong>Batch selesai!</strong>
            {{ $success }} berhasil, {{ $failed }} gagal.
            <a href="{{ route('ref-articles.index') }}" class="btn btn-success btn-md" style="margin-left:20px;">
                ← Kembali ke Manajemen Artikel
            </a>
        </div>
        @endif

        {{-- Failed Articles --}}
        @if($failedArticles->count() > 0)
        <div class="panel panel-danger" style="margin-bottom:20px;">
            <div class="panel-heading">
                <h3 class="panel-title">❌ Gagal ({{ $failedArticles->count() }})</h3>
            </div>
            <div class="panel-body">
                @foreach($failedArticles as $f)
                    <div class="error-item">
                        <div style="font-weight:600;">{{ Str::limit($f->title, 80) }}</div>
                        <small style="color:#dc3545;">Error: {{ Str::limit($f->ai_error, 120) }}</small>
                        <div style="margin-top:6px;">
                            <a href="{{ route('ref-articles.retry', $f) }}" class="btn btn-xs btn-warning">🔄 Retry</a>
                            <form action="{{ route('ref-articles.destroy', $f) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Hapus?')">🗑 Hapus</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Done Articles in Batch --}}
        @if($success > 0)
        <div class="panel panel-success" style="margin-bottom:20px;">
            <div class="panel-heading">
                <h3 class="panel-title">✅ Berhasil di-generate AI ({{ $success }})</h3>
            </div>
            <div class="panel-body" style="max-height:300px; overflow-y:auto;">
                @foreach(\App\Models\RefArticle::where('batch_id', $batchId)->where('ai_status', 'done')->get() as $a)
                    @if($a->generated_post_id)
                    <div style="padding:6px 0; border-bottom:1px solid #eee; display:flex; align-items:center; justify-content:space-between;">
                        <span>{{ Str::limit($a->title, 60) }}</span>
                        <a href="{{ route('posts.edit', $a->generated_post_id) }}" target="_blank" class="btn btn-xs btn-success">📰 Lihat Post</a>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        <a href="{{ route('ref-articles.index') }}" class="btn btn-default" style="margin-bottom:30px;">← Kembali ke Manajemen Artikel</a>
    </div>
</div>
@endsection
