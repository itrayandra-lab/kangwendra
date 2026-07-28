@extends('layouts.admin.app')
@section('title', 'Hasil Scrape')

@push('styles')
<style>
    .status-badge { display: inline-block; border-radius: 6px; padding: 3px 10px; font-size: 12px; font-weight: 600; }
    .status-pending { background:#fff3cd; color:#856404; }
    .status-success { background:#d4edda; color:#155724; }
    .status-failed { background:#f8d7da; color:#721c24; }
    .status-moved { background:#d1ecf1; color:#0c5460; }
    .stat-card { display:inline-block; background:#f8f9fa; border-radius:8px; padding:8px 14px; margin:3px; min-width:80px; text-align:center; }
    .stat-card .num { font-size:20px; font-weight:700; }
    .stat-card .label { font-size:11px; color:#888; }
    .result-row:hover { background:#f8f9fa; }
    .keyword-chip { display:inline-block; background:#e3f2fd; color:#1565c0; border-radius:20px; padding:2px 10px; font-size:12px; margin:2px; }
    .filter-tabs { margin-bottom:16px; }
    .filter-tab { display:inline-block; padding:5px 14px; border-radius:20px; margin-right:4px; text-decoration:none; color:#666; font-size:13px; border:1px solid #ddd; }
    .filter-tab:hover { border-color:#1565c0; color:#1565c0; }
    .filter-tab.active { background:#1565c0; color:#fff; border-color:#1565c0; }
    .select-all { cursor:pointer; }
</style>
@endpush

@section('content')

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            <i class="fa fa-search"></i> Hasil Scrape
        </h3>
    </div>
    <div class="panel-body">

        @if(session('success'))
            <div class="alert alert-success" style="border-radius:8px;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" style="border-radius:8px;">{{ session('error') }}</div>
        @endif

        {{-- Info Banner --}}
        <div style="background:#e8f5e9; border:1px solid #4caf50; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-size:13px; color:#2e7d32;">
            <strong>Flow:</strong> Research → Hasil Scrape → (pilih + "Pindahkan ke Ref Articles") → Ref Articles → Approve → Generate Post
        </div>

        {{-- Keyword Stats --}}
        @if(!empty($keywordStats))
            <div style="margin-bottom:16px;">
                <span style="font-size:12px; color:#888; margin-right:8px;">Per Keyword:</span>
                @foreach($keywordStats as $kw => $stat)
                    <span class="keyword-chip">
                        {{ $kw }}: {{ $stat['total'] }} total
                        <span style="color:#856404;">({{ $stat['pending'] }} pending)</span>
                        <span style="color:#155724;">({{ $stat['success'] }} sukses)</span>
                        <span style="color:#721c24;">({{ $stat['failed'] }} gagal)</span>
                    </span>
                @endforeach
            </div>
        @endif

        {{-- Filter Tabs --}}
        <div class="filter-tabs">
            <a href="{{ route('admin.scrape-results.index') }}" class="filter-tab {{ !$status ? 'active' : '' }}">Semua</a>
            <a href="{{ route('admin.scrape-results.index', ['status' => 'pending']) }}" class="filter-tab {{ $status === 'pending' ? 'active' : '' }}">Pending</a>
            <a href="{{ route('admin.scrape-results.index', ['status' => 'success']) }}" class="filter-tab {{ $status === 'success' ? 'active' : '' }}">Sukses</a>
            <a href="{{ route('admin.scrape-results.index', ['status' => 'failed']) }}" class="filter-tab {{ $status === 'failed' ? 'active' : '' }}">Gagal</a>
            <a href="{{ route('admin.scrape-results.index', ['status' => 'moved']) }}" class="filter-tab {{ $status === 'moved' ? 'active' : '' }}">Dipindahkan</a>
        </div>

        {{-- Bulk Actions + Table in one form (no nesting) --}}
        <form id="bulkForm" method="POST" action="{{ route('admin.scrape-results.move') }}">
            @csrf
            <div style="margin-bottom:12px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                <input type="checkbox" id="selectAll" style="margin-right:6px;">
                <label for="selectAll" style="font-size:13px; color:#666; margin-right:8px; cursor:pointer;">Pilih Semua</label>
                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Pindahkan yang dipilih ke Ref Articles?')">
                    <i class="fa fa-arrow-right"></i> Pindahkan ke Ref Articles
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()">
                    <i class="fa fa-trash"></i> Hapus Terpilih
                </button>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
            <table class="table table-striped table-hover" style="font-size:13px;">
                <thead>
                    <tr>
                        <th style="width:30px;"></th>
                        <th>Keyword</th>
                        <th>URL</th>
                        <th>Score</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $result)
                        <tr class="result-row">
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $result->id }}" class="row-check">
                            </td>
                            <td>
                                <span class="keyword-chip">{{ $result->keyword }}</span>
                            </td>
                            <td style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $result->url }}">
                                {{ $result->title ?? substr($result->url, 0, 50) }}
                            </td>
                            <td>
                                @if($result->confidence_score >= 70)
                                    <span style="color:#155724; font-weight:600;">{{ round($result->confidence_score) }}%</span>
                                @elseif($result->confidence_score >= 45)
                                    <span style="color:#856404;">{{ round($result->confidence_score) }}%</span>
                                @else
                                    <span style="color:#721c24;">{{ round($result->confidence_score) }}%</span>
                                @endif
                            </td>
                            <td>
                                @if($result->ref_article_id)
                                    <span class="status-badge status-moved">
                                        <i class="fa fa-check"></i> Dipindahkan
                                    </span>
                                @elseif($result->status === 'scrape_success')
                                    <span class="status-badge status-success">
                                        <i class="fa fa-check"></i> Sukses
                                    </span>
                                @elseif($result->status === 'scrape_failed')
                                    <span class="status-badge status-failed">
                                        <i class="fa fa-times"></i> Gagal
                                    </span>
                                @else
                                    <span class="status-badge status-pending">
                                        <i class="fa fa-clock-o"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td>{{ $result->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <a href="{{ $result->url }}" target="_blank" class="btn btn-xs btn-default" title="Buka URL">
                                    <i class="fa fa-external-link"></i>
                                </a>
                                @if(!$result->ref_article_id)
                                    <button type="button" class="btn btn-xs btn-success" title="Pindahkan ke Ref Articles"
                                        onclick="submitMove({{ $result->id }})">
                                        <i class="fa fa-arrow-right"></i>
                                    </button>
                                    <button type="button" class="btn btn-xs btn-danger" title="Hapus"
                                        onclick="submitDelete({{ $result->id }})">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                            <form method="POST" action="{{ route('admin.scrape-results.destroy') }}" style="display:inline;">
                                                @csrf
                                                <input type="hidden" name="ids[]" value="{{ $result->id }}">
                                                <button type="submit" class="btn btn-xs btn-danger" title="Hapus"
                                                    onclick="return confirm('Hapus?')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding:40px; color:#999;">
                                <i class="fa fa-inbox" style="font-size:40px;"></i>
                                <p style="margin-top:8px;">Belum ada hasil scrape. Klik keyword di Ref Articles untuk memulai research.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:12px;">
                {{ $results->withQueryString()->links() }}
            </div>
        </form>

        {{-- Hidden forms for individual actions --}}
        <form id="moveForm" method="POST" action="{{ route('admin.scrape-results.move') }}" style="display:none;">
            @csrf
            <div id="moveIdsContainer"></div>
        </form>
        <form id="deleteItemForm" method="POST" action="{{ route('admin.scrape-results.destroy') }}" style="display:none;">
            @csrf
            <div id="deleteItemIdsContainer"></div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Select all checkbox
    var selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('.row-check');
            checkboxes.forEach(function(cb) { cb.checked = selectAll.checked; });
        });
    }

    // Submit move form
    function submitMove(id) {
        if (!confirm('Pindahkan ke Ref Articles?')) return;
        var container = document.getElementById('moveIdsContainer');
        container.innerHTML = '<input type="hidden" name="ids[]" value="' + id + '">';
        document.getElementById('moveForm').submit();
    }

    // Submit delete form
    function submitDelete(id) {
        if (!confirm('Hapus hasil ini?')) return;
        var container = document.getElementById('deleteItemIdsContainer');
        container.innerHTML = '<input type="hidden" name="ids[]" value="' + id + '">';
        document.getElementById('deleteItemForm').submit();
    }

    // Bulk delete
    function bulkDelete() {
        if (!confirm('Hapus yang dipilih?')) return;
        var checked = document.querySelectorAll('.row-check:checked');
        if (checked.length === 0) { alert('Pilih至少 satu.'); return; }
        var container = document.getElementById('deleteItemIdsContainer');
        container.innerHTML = '';
        checked.forEach(function(cb) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });
        document.getElementById('deleteItemForm').submit();
    }
</script>
@endpush
