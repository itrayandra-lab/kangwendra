@extends('layouts.admin.app')
@section('title', $page)

@push('styles')
<style>
    .batch-container { max-width: 800px; margin: 0 auto; }
    .stat-row {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .stat-box {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 14px 20px;
        flex: 1;
        min-width: 100px;
        text-align: center;
    }
    .stat-box .num { font-size: 28px; font-weight: 700; }
    .stat-box .lbl { font-size: 12px; color: #888; margin-top: 2px; }
    .stat-ok .num { color: #28a745; }
    .stat-err .num { color: #dc3545; }
    .stat-proc .num { color: #ffc107; }
    .prog-bar {
        background: #e9ecef;
        border-radius: 4px;
        height: 8px;
        margin-bottom: 16px;
        overflow: hidden;
    }
    .prog-bar-fill {
        height: 100%;
        background: #28a745;
        transition: width 0.5s;
    }
    .section {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 16px;
        margin-bottom: 16px;
    }
    .section-title {
        font-size: 13px;
        font-weight: 600;
        color: #333;
        margin: 0 0 12px;
        border-bottom: 1px solid #eee;
        padding-bottom: 8px;
    }
    .item-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f5f5f5;
        font-size: 13px;
    }
    .item-row:last-child { border-bottom: none; }
    .err-row {
        padding: 10px 12px;
        background: #fff5f5;
        border-left: 3px solid #dc3545;
        border-radius: 4px;
        margin-bottom: 8px;
        font-size: 13px;
    }
    .err-title { font-weight: 600; margin-bottom: 2px; }
    .err-msg { color: #dc3545; font-size: 12px; }
</style>
@endpush

@push('scripts')
<script>
let batchId = "{{ $batchId ?? '' }}";
let poll;

function refresh() {
    if (!batchId) return;
    fetch("{{ route('ref-articles.batch-status') }}?batch_id=" + batchId)
        .then(r => r.json())
        .then(d => {
            if (d.error) return;
            let total = d.total || 0;
            let ok = d.success || 0;
            let fail = d.failed || 0;
            let done = ok + fail;
            document.getElementById('s-total').textContent = total;
            document.getElementById('s-ok').textContent = ok;
            document.getElementById('s-fail').textContent = fail;
            document.getElementById('s-proc').textContent = total - done;
            let pct = total > 0 ? Math.round((done / total) * 100) : 0;
            document.getElementById('prog-fill').style.width = pct + '%';
            document.getElementById('prog-pct').textContent = pct + '%';
            if (d.status === 'complete') {
                document.getElementById('s-status').textContent = 'Selesai';
                clearInterval(poll);
            } else {
                document.getElementById('s-status').textContent = 'Diproses (' + done + '/' + total + ')';
            }
        })
        .catch(() => {});
}

if (batchId) {
    refresh();
    poll = setInterval(refresh, 3000);
}
</script>
@endpush

@section('content')
<div class="batch-container">

    <div style="margin-bottom: 20px;">
        <a href="{{ route('ref-articles.index') }}" class="btn btn-default btn-sm">&larr; Kembali</a>
        <span style="margin-left: 12px; font-size: 16px; font-weight: 600;">Progress Generate AI</span>
        <span id="s-status" style="margin-left: 12px; font-size: 13px; color: #888;">
            {{ ($success + $failed) == $total && $total > 0 ? 'Selesai' : 'Diproses (' . ($success + $failed) . '/' . $total . ')' }}
        </span>
    </div>

    {{-- Stats --}}
    <div class="stat-row">
        <div class="stat-box">
            <div class="num" id="s-total">{{ $total }}</div>
            <div class="lbl">Total</div>
        </div>
        <div class="stat-box stat-ok">
            <div class="num" id="s-ok">{{ $success }}</div>
            <div class="lbl">Berhasil</div>
        </div>
        <div class="stat-box stat-err">
            <div class="num" id="s-fail">{{ $failed }}</div>
            <div class="lbl">Gagal</div>
        </div>
        <div class="stat-box stat-proc">
            <div class="num" id="s-proc">{{ $processing + $pending }}</div>
            <div class="lbl">Proses</div>
        </div>
    </div>

    {{-- Progress bar --}}
    @if($total > 0)
    <div class="prog-bar">
        <div class="prog-bar-fill" id="prog-fill" style="width: {{ round((($success + $failed) / $total) * 100) }}%"></div>
    </div>
    <div style="text-align: right; font-size: 12px; color: #888; margin-bottom: 20px;">
        <span id="prog-pct">{{ round((($success + $failed) / $total) * 100) }}%</span>
    </div>
    @endif

    {{-- Completion --}}
    @if(($success + $failed) == $total && $total > 0)
    <div class="section">
        <div style="text-align: center; padding: 10px;">
            <strong>Batch selesai.</strong> {{ $success }} berhasil, {{ $failed }} gagal.
            &nbsp;
            <a href="{{ route('ref-articles.index') }}" class="btn btn-primary btn-sm">Kembali ke Manajemen</a>
        </div>
    </div>
    @endif

    {{-- Failed --}}
    @if($failedArticles->count() > 0)
    <div class="section">
        <div class="section-title">Gagal ({{ $failedArticles->count() }})</div>
        @foreach($failedArticles as $f)
            <div class="err-row">
                <div class="err-title">{{ Str::limit($f->title, 70) }}</div>
                <div class="err-msg">{{ Str::limit($f->ai_error, 120) }}</div>
                <div style="margin-top: 6px;">
                    <a href="{{ route('ref-articles.retry', $f) }}" class="btn btn-warning btn-xs">Retry</a>
                    <form action="{{ route('ref-articles.destroy', $f) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Hapus?')">Hapus</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    {{-- Done --}}
    @if($success > 0)
    <div class="section">
        <div class="section-title">Berhasil di-generate ({{ $success }})</div>
        @foreach(\App\Models\RefArticle::where('batch_id', $batchId)->where('ai_status', 'done')->get() as $a)
            @if($a->generated_post_id)
            <div class="item-row">
                <span>{{ Str::limit($a->title, 55) }}</span>
                <a href="{{ route('posts.edit', $a->generated_post_id) }}" target="_blank" class="btn btn-success btn-xs">Edit Post</a>
            </div>
            @endif
        @endforeach
    </div>
    @endif

</div>
@endsection
