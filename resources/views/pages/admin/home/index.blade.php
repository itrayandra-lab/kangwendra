@extends('layouts.admin.app')
@section('title', $page)
@push('styles')
    <style>
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .card-icon {
            font-size: 2rem;
            color: #fff;
        }

        .card-title {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .card-value {
            font-size: 2rem;
            font-weight: bold;
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="row">
            <div class="col-sm-6 col-lg-3">
                <div class="panel panel-primary text-center">
                    <div class="panel-heading">
                        <h4 class="panel-title">Jumlah Pengguna</h4>
                    </div>
                    <div class="panel-body">
                        <h3 class=""><b>{{ $totalUsers }}</b></h3>
                        <p class="text-muted">Total akun pengguna terdaftar</p>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="panel panel-primary text-center">
                    <div class="panel-heading">
                        <h4 class="panel-title">Jumlah Berita</h4>
                    </div>
                    <div class="panel-body">
                        <h3 class=""><b>{{ $totalNews }}</b></h3>
                        <p class="text-muted">Total publikasi berita</p>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="panel panel-primary text-center">
                    <div class="panel-heading">
                        <h4 class="panel-title">Berita Tahun Ini</h4>
                    </div>
                    <div class="panel-body">
                        <h3 class=""><b>{{ $newsThisYear }}</b></h3>
                        <p class="text-muted">Publikasi tahun {{ now()->format('Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="panel panel-primary text-center">
                    <div class="panel-heading">
                        <h4 class="panel-title">Berita Hari Ini</h4>
                    </div>
                    <div class="panel-body">
                        <h3 class=""><b>{{ $newsToday }}</b></h3>
                        <p class="text-muted">Publikasi tanggal {{ now()->translatedFormat('d F Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
@endpush


