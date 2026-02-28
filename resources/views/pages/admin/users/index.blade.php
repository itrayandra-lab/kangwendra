@extends('layouts.admin.app')
@section('title', 'Manajemen Users')

@push('styles')

@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Manajemen Users</h3>
            </div>
            <div class="panel-body">

                @can('create users')
                    <a href="{{ route('users.create') }}" class="btn btn-success btn-sm mb-3">
                        <i class="fa fa-plus"></i> Tambah User
                    </a>
                @endcan

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">×</button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert">×</button>
                    </div>
                @endif

                <table id="users-table" class="table table-striped table-bordered dt-responsive nowrap" width="100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Tautan</th>
                            <th>Email</th>
                            <th>Gambar</th>
                            <th>Status</th>
                            <th>Role</th>
                            <th>No. Telepon</th>
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
    $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('users.index') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'profile_link', name: 'profile_link', orderable: false, searchable: false },
            { data: 'email', name: 'email' },
            { data: 'image', name: 'image', orderable: false, searchable: false },
            { data: 'status', name: 'status' },
            { data: 'role', name: 'role' },
            { data: 'phone_number', name: 'phone_number', defaultContent: '-' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });
});
</script>
@endpush

