@extends('layouts.admin.app')
@section('title', $page)
@push('styles')
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Management {{ $page }}</h3>
                </div>
                <div class="panel-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {!! session('success') !!}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    <div class="panel-action mb-4">
                        <div class="alert alert-warning">
                            <strong>DINONAKTIFKAN:</strong> Tombol RSS langsung save ke Posts tanpa AI paraphrase (copyright risk).
                            Gunakan <code>app:auto-feed --scrape-only</code> sebagai gantinya.
                        </div>
                    </div>
                    <hr>
                    <div class="alert alert-info mb-4">
                        <strong>Info:</strong> RSS Yahoo AI <strong>DINONAKTIFKAN</strong>. Pipeline otomatis menggunakan
                        <code>app:auto-feed --scrape-only</code> (scrape → RefArticle → AI paraphrase → Post).
                    </div>

                    <!-- Livewire Component for Yahoo News Table -->
                    @livewire('yahoo-news')
                </div>
            </div>
        </div>
    </div>
@endsection