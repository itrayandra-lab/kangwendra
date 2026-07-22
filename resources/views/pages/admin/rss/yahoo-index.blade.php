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
                        <form action="{{ route('rss.fetch-yahoo') }}" method="POST" class="form-inline">
                            @csrf
                            <div class="form-group mr-2">
                                <label for="date" class="mr-2">Tanggal (opsional):</label>
                                <input type="date" name="date" id="date" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fa fa-refresh"></i> Ambil RSS Yahoo AI Sekarang
                            </button>
                        </form>
                    </div>
                    <hr>
                    <div class="alert alert-info mb-4">
                        <strong>Info:</strong> RSS Yahoo AI akan diambil dari Yahoo News dan Yahoo Tech.
                    </div>

                    <!-- Livewire Component for Yahoo News Table -->
                    @livewire('yahoo-news')
                </div>
            </div>
        </div>
    </div>
@endsection