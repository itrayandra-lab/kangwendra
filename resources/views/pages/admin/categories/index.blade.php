@extends('layouts.admin.app')
@section('title', 'Manajemen Kategori')

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
                <h3 class="panel-title">Manajemen Kategori</h3>
            </div>
            <div class="panel-body">

                @can('create categories')
                    <a href="{{ route('categories.create') }}" class="btn btn-success btn-sm mb-3">
                        <i class="fa fa-plus"></i> Tambah Kategori
                    </a>
                @endcan

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">×</button>
                    </div>
                @endif

                <table id="categories-table" class="table table-striped table-bordered dt-responsive nowrap" width="100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
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
    $('#categories-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('categories.index') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });
});
</script>
@endpush

