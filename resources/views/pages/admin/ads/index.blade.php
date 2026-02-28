@extends('layouts.admin.app')
@section('title', 'Manajemen Iklan')

@push('styles')
<style>
    .switch { position: relative; display: inline-block; width: 48px; height: 24px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: #28a745; }
    input:checked + .slider:before { transform: translateX(24px); }
    .slider.round { border-radius: 34px; }
    .slider.round:before { border-radius: 50%; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Manajemen Iklan</h3>
            </div>
            <div class="panel-body">

                @can('create ads')
                    <a href="{{ route('ads.create') }}" class="btn btn-success btn-sm mb-3">
                        <i class="fa fa-plus"></i> Tambah Iklan
                    </a>
                @endcan

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }} <button type="button" class="close" data-dismiss="alert">×</button>
                    </div>
                @endif

                <table id="ads-table" class="table table-striped table-bordered dt-responsive nowrap" width="100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Preview</th>
                            <th>Judul</th>
                            <th>Tipe</th>
                            <th>Redirect URL</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')

<script>
$(document).ready(function() {
    const table = $('#ads-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('ads.index') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'preview', name: 'preview', orderable: false, searchable: false },
            { data: 'title', name: 'title' },
            { data: 'type', name: 'type' },
            { data: 'redirect_url', name: 'redirect_url' },
            { data: 'is_active', name: 'is_active', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });
});
</script>
@endpush

