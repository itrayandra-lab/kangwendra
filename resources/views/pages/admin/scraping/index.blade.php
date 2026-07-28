@extends('layouts.admin.app')
@section('title', 'Scraping')

@push('styles')
<style>
    .action-btn {
        border-radius: 8px;
        padding: 8px 16px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        font-size: 13px;
    }
    .btn-research { background: #198754; color: white; }
    .btn-research:hover { background: #157347; }
    .btn-research-all { background: #0d6efd; color: white; }
    .btn-research-all:hover { background: #0b5ed7; }
</style>
@endpush

@section('content')

<div class="row">
    <div class="col-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Scraping Artikel AI</h3>
            </div>
            <div class="panel-body">

                @if(session('success'))
                    <div class="alert alert-success" style="border-radius:8px;">{!! session('success') !!}</div>
                @endif

                {{-- Info --}}
                <div style="background:#e3f2fd; border:1px solid #1565c0; border-radius:8px; padding:12px 16px; margin-bottom:20px; font-size:13px; color:#1565c0;">
                    <strong>Flow:</strong> Pilih keyword di bawah → klik <strong>Research</strong> → hasil muncul di <strong>Hasil Scraping</strong> → pilih → <strong>Pindahkan ke Ref Articles</strong> → <strong>Ref Articles</strong> → Approve → <strong>Postingan AI</strong>
                </div>

                {{-- Scrape All --}}
                <div style="background:#fff3cd; border:1px solid #ffc107; border-radius:8px; padding:16px; margin-bottom:20px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                        <div>
                            <strong>Scrape Semua Keyword Sekaligus</strong>
                            <p style="margin:4px 0 0; color:#856404; font-size:13px;">
                                Klik tombol di bawah untuk scrape semua keyword dalam 1x proses via queue worker.
                            </p>
                        </div>
                        <form action="{{ route('admin.scraping.research-all') }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="action-btn btn-research-all"
                                onclick="return confirm('Scrape semua keyword sekarang? Queue worker akan proses di background.')">
                                <i class="fa fa-bolt"></i> Scrape Semua Keyword
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Quick Topic Buttons --}}
                <div style="margin-bottom:16px;">
                    <div style="font-size:13px; color:#888; margin-bottom:8px;">Pilih 1 keyword:</div>
                    <div style="display:flex; flex-wrap:wrap; gap:6px;">
                        @foreach($keywords as $topic)
                            <form action="{{ route('admin.scraping.research') }}" method="POST" style="display:inline;">
                                @csrf
                                <input type="hidden" name="keyword" value="{{ $topic }}">
                                <button type="submit" class="btn btn-outline-primary btn-sm" style="border-radius:15px; padding:5px 14px; font-size:12px;">
                                    {{ $topic }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>

                {{-- Manual Input --}}
                <div style="margin-bottom:16px;">
                    <div style="font-size:13px; color:#888; margin-bottom:8px;">Atau ketik keyword baru:</div>
                    <div style="display:flex; gap:8px; max-width:500px;">
                        <form action="{{ route('admin.scraping.research') }}" method="POST" style="display:flex; gap:8px; flex:1;">
                            @csrf
                            <input type="text" name="keyword" class="form-control"
                                placeholder="Ketik topic baru (contoh: Grok, Perplexity, AI coding)"
                                required style="border-radius:8px;">
                            <button type="submit" class="action-btn btn-research">
                                <i class="fa fa-search"></i> Research
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Quick Stats (Scraping pipeline only) --}}
                <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:20px; padding-top:16px; border-top:1px solid #eee;">
                    <div style="background:#f8f9fa; border-radius:8px; padding:10px 16px; text-align:center;">
                        <div style="font-size:22px; font-weight:700; color:#333;">{{ $stats['pending'] }}</div>
                        <div style="font-size:11px; color:#888;">Menunggu Approve/Reject</div>
                    </div>
                    <div style="background:#fff3cd; border-radius:8px; padding:10px 16px; text-align:center;">
                        <div style="font-size:22px; font-weight:700; color:#856404;">{{ $stats['moved'] }}</div>
                        <div style="font-size:11px; color:#856404;">Sudah Dipindahkan</div>
                    </div>
                    <div style="background:#d1ecf1; border-radius:8px; padding:10px 16px; text-align:center;">
                        <div style="font-size:22px; font-weight:700; color:#0c5460;">{{ $stats['ref_articles_total'] }}</div>
                        <div style="font-size:11px; color:#0c5460;">Total Ref Articles</div>
                    </div>
                </div>

                {{-- Queue Worker Info --}}
                <div style="margin-top:20px; padding:12px; background:#f8f9fa; border-radius:8px; font-size:12px; color:#888;">
                    <i class="fa fa-info-circle"></i>
                    <strong>Tips:</strong> Pastikan queue worker jalan di terminal untuk memproses jobs:
                    <code>php artisan queue:work</code>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
