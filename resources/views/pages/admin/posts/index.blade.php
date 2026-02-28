@extends('layouts.admin.app')
@section('title', 'Manajemen Postingan')

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
                <h3 class="panel-title">Manajemen Postingan</h3>
            </div>
            <div class="panel-body">

                @can('create posts')
                    <a href="{{ route('posts.create') }}" class="btn btn-success btn-sm mb-3">
                        <i class="fa fa-plus"></i> Tambah Postingan
                    </a>
                @endcan

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">×</button>
                    </div>
                @endif

                <table id="posts-table" class="table table-striped table-bordered dt-responsive nowrap" width="100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Link</th>
                            <th>Judul</th>
                            <th>Gambar</th>
                            <th>Views</th>
                            <th>Status</th>
                            <th>Kategori</th>
                            <th>Tags</th>
                            <th>Dibuat Oleh</th>
                            <th>Diperbarui Oleh</th>
                            <th>Dipublikasikan</th>
                            <th>Dibuat Pada</th>
                            <th>Diperbarui Pada</th>
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
    $('#posts-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('posts.index') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'link', name: 'link', orderable: false, searchable: false },
            { data: 'title', name: 'title' },
            { data: 'image', name: 'image', orderable: false, searchable: false },
            { data: 'counter', name: 'counter' },
            { data: 'status', name: 'status' },
            { data: 'category', name: 'category' },
            { data: 'tags', name: 'tags', orderable: false },
            { data: 'created_by', name: 'created_by' },
            { data: 'updated_by', name: 'updated_by' },
            { data: 'published_at', name: 'published_at' },
            { data: 'created_at', name: 'created_at' },
            { data: 'updated_at', name: 'updated_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[11, 'desc']] // urutkan default berdasarkan created_at desc
    });
});
</script>
@endpush

