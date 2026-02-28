@extends('layouts.admin.app')
@section('title', 'Manajemen Domain Share')

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
                <h3 class="panel-title">Manajemen Domain Share</h3>
            </div>
            <div class="panel-body">

                @can('create domain-share')
                    <a href="{{ route('domain-share.create') }}" class="btn btn-success btn-sm mb-3">
                        <i class="fa fa-plus"></i> Tambah Domain
                    </a>
                @endcan

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">×</button>
                    </div>
                @endif

                <table id="domain-share-table" class="table table-striped table-bordered dt-responsive nowrap" width="100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Domain</th>
                            <th>Webhook URL</th>
                            <th>Status</th>
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
    $('#domain-share-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('domain-share.index') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'domain_name', name: 'domain_name' },
            { data: 'webhook_url', name: 'webhook_url' },
            { data: 'status', name: 'status' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[4, 'desc']] 
    });
});
</script>
@endpush

