@extends('layouts.admin.app')
@section('title', $page)

@push('styles')
<style>
    .status-badge { display:inline-block; border-radius:6px; padding:3px 10px; font-size:12px; font-weight:600; }
    .status-pending { background:#fff3cd; color:#856404; }
    .status-moved { background:#d1ecf1; color:#0c5460; }
    .keyword-chip { display:inline-block; background:#e3f2fd; color:#1565c0; border-radius:20px; padding:2px 10px; font-size:12px; margin:2px; }
    .keyword-filter-bar { background:#fff8e1; border:1px solid #ffc107; border-radius:8px; padding:12px 16px; margin-bottom:16px; }
    .filter-tab { display:inline-block; padding:5px 14px; border-radius:20px; margin-right:4px; text-decoration:none; color:#666; font-size:13px; border:1px solid #ddd; }
    .filter-tab:hover { border-color:#1565c0; color:#1565c0; }
    .filter-tab.active { background:#1565c0; color:#fff; border-color:#1565c0; }
    .confidence-legend { display:flex; gap:16px; flex-wrap:wrap; margin-top:8px; }
    .confidence-item { display:flex; align-items:center; gap:6px; font-size:12px; }
    .confidence-dot { width:10px; height:10px; border-radius:50%; }
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

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success" style="border-radius:8px;">{{ session('success') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning" style="border-radius:8px;">{{ session('warning') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" style="border-radius:8px;">{{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="alert alert-info" style="border-radius:8px;">{!! session('info') !!}</div>
        @endif

        {{-- Batch Progress Link (if user just clicked research) --}}
        @if(request('batch_processed'))
            <div style="background:#d4edda; border:1px solid #28a745; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-size:13px; color:#155724;">
                <i class="fa fa-check-circle" style="color:#155724;"></i>
                <strong>Research selesai!</strong> Berikut hasil dari research terakhir. Approve URL yang kamu mau untuk dipindahkan ke Ref Articles.
                <a href="{{ route('admin.scraping.batch-progress') }}" class="btn btn-xs btn-success" style="margin-left:8px; border-radius:20px;">
                    Lihat Progress Research →
                </a>
            </div>
        @endif

        {{-- Keyword Alternatives (jika 0 results) --}}
        @if($results->isEmpty() && !empty($altKeywords))
            <div style="background:#fff3cd; border:1px solid #ffc107; border-radius:8px; padding:16px 20px; margin-bottom:16px; font-size:13px;">
                <strong style="color:#856404;">
                    <i class="fa fa-search"></i>
                    Tidak ada hasil untuk
                    @if($keyword)
                        keyword "<strong>{{ $keyword }}</strong>"
                    @else
                        research ini
                    @endif
                    saat ini.
                </strong>
                <p style="color:#856404; margin:8px 0 4px;">
                    Keyword yang punya hasil terbaru:
                </p>
                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    @foreach($altKeywords as $altKw)
                        <form action="{{ route('admin.scraping.research') }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="keyword" value="{{ $altKw }}">
                            <button type="submit" class="btn btn-xs"
                                style="background:#fff; border:1px solid #ffc107; color:#856404; border-radius:20px; cursor:pointer; padding:4px 12px;">
                                <i class="fa fa-search"></i> {{ $altKw }}
                        </form>
                    @endforeach
                </div>
                <p style="color:#856404; margin:8px 0 0; font-size:12px;">
                    Atau klik <a href="{{ route('admin.scraping.index') }}">menu Scraping</a> untuk research keyword baru.
                </p>
            </div>
        @endif

        {{-- Flow Banner --}}
        <div style="background:#e8f5e9; border:1px solid #4caf50; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-size:13px; color:#2e7d32;">
            <strong>Flow:</strong> Scraping → Research keyword → hasil URL di sini → pilih → <strong>[Approve]</strong> atau <strong>[Reject]</strong>
        </div>

        {{-- Keyword Filter Bar --}}
        @if($keyword)
            <div class="keyword-filter-bar">
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
                    <div>
                        <strong><i class="fa fa-filter"></i> Filter Keyword:</strong>
                        <span style="background:#fff; border:1px solid #ffc107; border-radius:20px; padding:4px 12px; margin-left:8px; font-weight:600; color:#856404;">
                            {{ $keyword }}
                        </span>
                        <span style="color:#888; font-size:12px; margin-left:8px;">
                            — menampilkan hasil hanya untuk keyword ini
                        </span>
                    </div>
                    <a href="{{ route('admin.hasil-scraping.index') }}" class="btn btn-warning btn-sm">
                        <i class="fa fa-times"></i> Tampilkan Semua
                    </a>
                </div>
            </div>
        @endif

        {{-- AI Confidence Info Banner --}}
        <div style="background:#e3f2fd; border:1px solid #1976d2; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-size:13px; color:#1565c0;">
            <strong><i class="fa fa-info-circle"></i> Cara Confidence Score Dihitung:</strong>
            <div style="margin-top:6px; padding-left:8px;">
                +70 poin jika <strong>keyword ada di URL</strong> &nbsp;|&nbsp;
                +5 jika <strong>AI keyword lain</strong> terdeteksi &nbsp;|&nbsp;
                +3 jika domain <strong>SEJ/SEL</strong> &nbsp;|&nbsp;
                <span style="color:#dc3545;">-10 sd -20</span> jika ada pola <strong>review/comparison/brand HP</strong>
            </div>
            <div class="confidence-legend">
                <span class="confidence-item"><span class="confidence-dot" style="background:#198754;"></span> 70-80% = Sangat relevan</span>
                <span class="confidence-item"><span class="confidence-dot" style="background:#fd7e14;"></span> 45-69% = Cukup relevan</span>
                <span class="confidence-item"><span class="confidence-dot" style="background:#dc3545;"></span> 20-44% = Kurang relevan</span>
                <span class="confidence-item"><span class="confidence-dot" style="background:#999;"></span> &lt;20% = Otomatis discan (tidak tampil)</span>
            </div>
        </div>

        {{-- Keyword Stats --}}
        @if(!empty($keywordStats))
            <div style="margin-bottom:16px;">
                <span style="font-size:12px; color:#888; margin-right:8px;">Per Keyword:</span>
                @foreach($keywordStats as $kw => $stat)
                    <span class="keyword-chip">
                        {{ $kw }}:
                        <strong>{{ $stat['total'] }}</strong> total,
                        <span style="color:#856404;">{{ $stat['pending'] }} pending</span>,
                        <span style="color:#0c5460;">{{ $stat['moved'] }} dipindahkan</span>
                    </span>
                @endforeach
            </div>
        @endif

        {{-- Pagination info --}}
        <div style="font-size:12px; color:#888; margin-bottom:8px;">
            Menampilkan {{ $results->count() }} hasil per halaman.
            @if($results->hasPages())
                Total {{ $results->total() }} hasil.
            @endif
        </div>
        <div style="margin-bottom:16px;">
            <a href="{{ $keyword ? route('admin.hasil-scraping.index', ['keyword' => $keyword]) : route('admin.hasil-scraping.index') }}"
               class="filter-tab {{ !$status ? 'active' : '' }}">Semua</a>
            <a href="{{ $keyword ? route('admin.hasil-scraping.index', ['keyword' => $keyword, 'status' => 'pending']) : route('admin.hasil-scraping.index', ['status' => 'pending']) }}"
               class="filter-tab {{ $status === 'pending' ? 'active' : '' }}">Pending</a>
            <a href="{{ $keyword ? route('admin.hasil-scraping.index', ['keyword' => $keyword, 'status' => 'moved']) : route('admin.hasil-scraping.index', ['status' => 'moved']) }}"
               class="filter-tab {{ $status === 'moved' ? 'active' : '' }}">Dipindahkan</a>
            <span style="float:right;">
                <a href="{{ route('admin.scraping.batch-progress') }}" class="btn btn-xs btn-default" style="border-radius:20px;">
                    <i class="fa fa-tasks"></i> Progress Research
                </a>
            </span>
        </div>

        {{-- Bulk Actions --}}
        <form id="bulkForm" method="POST" action="{{ route('admin.hasil-scraping.approve') }}">
            @csrf
            <div style="margin-bottom:12px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                <input type="checkbox" id="selectAll" style="margin-right:6px;">
                <label for="selectAll" style="font-size:13px; color:#666; margin-right:8px; cursor:pointer;">Pilih Semua</label>

                <button type="submit" class="btn btn-success btn-sm"
                    onclick="return confirm('Approve yang dipilih? URL akan dipindahkan ke Ref Articles dan confidence keyword dinaikkan.')">
                    <i class="fa fa-check"></i> Approve Terpilih
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="bulkReject()">
                    <i class="fa fa-times"></i> Reject Terpilih
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="bulkDelete()">
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
                        <tr>
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $result->id }}" class="row-check">
                            </td>
                            <td>
                                <span class="keyword-chip">{{ $result->keyword }}</span>
                            </td>
                            <td style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $result->url }}">
                                {{ Str::limit($result->title ?? $result->url, 60) }}
                            </td>
                            <td>
                                @if($result->confidence_score >= 70)
                                    <span style="color:#198754; font-weight:700;">{{ round($result->confidence_score) }}%</span>
                                @elseif($result->confidence_score >= 45)
                                    <span style="color:#fd7e14; font-weight:600;">{{ round($result->confidence_score) }}%</span>
                                @else
                                    <span style="color:#dc3545;">{{ round($result->confidence_score) }}%</span>
                                @endif
                            </td>
                            <td>
                                @if($result->ref_article_id)
                                    <span class="status-badge status-moved"><i class="fa fa-check"></i> Dipindahkan</span>
                                @else
                                    <span class="status-badge status-pending"><i class="fa fa-clock-o"></i> Pending</span>
                                @endif
                            </td>
                            <td>{{ $result->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <a href="{{ $result->url }}" target="_blank" class="btn btn-xs btn-default" title="Buka URL">
                                    <i class="fa fa-external-link"></i>
                                </a>
                                @if(!$result->ref_article_id)
                                    <button type="button" class="btn btn-xs btn-success" title="Approve"
                                        onclick="submitApprove({{ $result->id }})">
                                        <i class="fa fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-xs btn-danger" title="Reject"
                                        onclick="submitReject({{ $result->id }})">
                                        <i class="fa fa-times"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding:40px;">
                                <i class="fa fa-inbox" style="font-size:40px; color:#ccc;"></i>
                                @if($keyword)
                                    <p style="margin-top:12px; color:#856404; font-weight:600;">
                                        Belum ada hasil untuk "<strong>{{ $keyword }}</strong>".
                                    </p>
                                    <p style="color:#888; font-size:13px; margin-top:8px;">
                                        Kemungkinan sitemap SEJ/SEL tidak memuat artikel tentang "{{ $keyword }}" saat ini.
                                    </p>
                                @else
                                    <p style="margin-top:12px; color:#888;">
                                        Belum ada hasil scrape.
                                        <br>Klik <strong>Research</strong> di menu Scraping untuk memulai.
                                    </p>
                                @endif
                                @if(!empty($altKeywords))
                                    <p style="margin-top:16px; color:#856404; font-size:13px;">
                                        Keyword yang punya hasil terbaru:
                                    </p>
                                    <div style="margin-top:8px;">
                                        @foreach($altKeywords as $altKw)
                                            <form action="{{ route('admin.scraping.research') }}" method="POST" style="display:inline;">
                                                @csrf
                                                <input type="hidden" name="keyword" value="{{ $altKw }}">
                                                <button type="submit" class="btn btn-xs"
                                                    style="background:#fff3cd; border:1px solid #ffc107; color:#856404; border-radius:20px; cursor:pointer; margin:2px;">
                                                    <i class="fa fa-search"></i> {{ $altKw }}
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                @endif
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
        <form id="approveForm" method="POST" action="{{ route('admin.hasil-scraping.approve') }}" style="display:none;">
            @csrf
            <div id="approveIdsContainer"></div>
        </form>
        <form id="rejectForm" method="POST" action="{{ route('admin.hasil-scraping.reject') }}" style="display:none;">
            @csrf
            <div id="rejectIdsContainer"></div>
        </form>
        <form id="deleteItemForm" method="POST" action="{{ route('admin.hasil-scraping.destroy') }}" style="display:none;">
            @csrf
            <div id="deleteItemIdsContainer"></div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    var selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.row-check').forEach(function(cb) { cb.checked = selectAll.checked; });
        });
    }

    function submitApprove(id) {
        if (!confirm('Approve URL ini? Akan dipindahkan ke Ref Articles dan confidence keyword dinaikkan.')) return;
        var container = document.getElementById('approveIdsContainer');
        container.innerHTML = '<input type="hidden" name="ids[]" value="' + id + '">';
        document.getElementById('approveForm').submit();
    }

    function submitReject(id) {
        if (!confirm('Reject URL ini? Akan dihapus dari database dan masuk blocklist sementara.')) return;
        var container = document.getElementById('rejectIdsContainer');
        container.innerHTML = '<input type="hidden" name="ids[]" value="' + id + '">';
        document.getElementById('rejectForm').submit();
    }

    function bulkReject() {
        if (!confirm('Reject yang dipilih? Akan dihapus dan masuk blocklist sementara.')) return;
        var checked = document.querySelectorAll('.row-check:checked');
        if (checked.length === 0) { alert('Pilih setidaknya satu.'); return; }
        var container = document.getElementById('rejectIdsContainer');
        container.innerHTML = '';
        checked.forEach(function(cb) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });
        document.getElementById('rejectForm').submit();
    }

    function bulkDelete() {
        if (!confirm('Hapus yang dipilih?')) return;
        var checked = document.querySelectorAll('.row-check:checked');
        if (checked.length === 0) { alert('Pilih setidaknya satu.'); return; }
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
