@extends('layouts.admin.app')
@section('title', $page)
@push('styles')
   
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Manajemen {{ $page }}</h3>
                </div>
                <div class="panel-body">
                    @can('manage permissions')
                        <div class="panel-action">
                            <a href="{{ route('permissions.create') }}" class="btn btn-success btn-sm">
                                <i class="fa fa-plus"></i> Tambah Permission
                            </a>
                        </div>
                    @else
                        <p class="text-muted">Anda tidak memiliki izin untuk menambah permission.</p>
                    @endcan

                    @can('manage permissions')
                        <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permissions as $permission)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $permission->name }}</td>
                                        <td>
                                            @can('manage permissions')
                                                <a href="{{ route('permissions.edit', $permission->id) }}" class="btn btn-primary btn-xs">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                                <form action="{{ route('permissions.destroy', $permission->id) }}" method="POST" style="display: inline;"
                                                    onsubmit="return confirmDelete(this)">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs">
                                                        <i class="fa fa-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-danger">Anda tidak memiliki izin untuk melihat daftar permission.</p>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection


