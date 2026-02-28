@extends('layouts.admin.app')
@section('title', 'Manajemen Galery')

@push('styles')
<style>
    .text-center .btn { margin: 0 2px; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Manajemen Galery</h3>
            </div>
            <div class="panel-body">

                @can('create album')
                    <a href="{{ route('album.create') }}" class="btn btn-success btn-sm mb-3">
                        <i class="fa fa-plus"></i> Tambah Album
                    </a>
                @endcan

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">×</button>
                    </div>
                @endif

                <table id="albums-table" class="table table-striped table-bordered dt-responsive nowrap" width="100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tautan</th>
                            <th>Nama Album</th>
                            <th>Jumlah Foto</th>
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
    $('#albums-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('album.index') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'link', name: 'link', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'photo_count', name: 'photo_count', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });
});
</script>
@endpush

