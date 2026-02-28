@extends('layouts.admin.app')
@section('title', 'Manajemen Informasi')

@push('styles')
<style>
    .text-center .btn { margin: 0 2px; }
    .filter-box { max-width: 250px; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Manajemen Informasi</h3>
            </div>
            <div class="panel-body">

                @can('create information')
                    <a href="{{ route('information.create') }}" class="btn btn-success btn-sm mb-3">
                        <i class="fa fa-plus"></i> Tambah Informasi
                    </a>
                @endcan

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">×</button>
                    </div>
                @endif

                <!-- Filter Tipe -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select id="type_filter" class="form-control filter-box">
                            <option value="">Semua Tipe</option>
                            <option value="banner" {{ request('type') == 'banner' ? 'selected' : '' }}>Banner</option>
                            <option value="text" {{ request('type') == 'text' ? 'selected' : '' }}>Text</option>
                        </select>
                    </div>
                </div>

                <table id="information-table" class="table table-striped table-bordered dt-responsive nowrap" width="100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Link</th>
                            <th>Gambar</th>
                            <th>Tipe</th>
                            <th>Dibuat Oleh</th>
                            <th>Dibuat Pada</th>
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
    const table = $('#information-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('information.index') }}',
            data: function (d) {
                d.type = $('#type_filter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'title', name: 'title' },
            { data: 'link', name: 'link', orderable: false, searchable: false },
            { data: 'image', name: 'image', orderable: false, searchable: false },
            { data: 'type', name: 'type' },
            { data: 'created_by', name: 'created_by' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[6, 'desc']] 
    });

    $('#type_filter').on('change', function() {
        table.ajax.reload();
    });
});
</script>
@endpush

