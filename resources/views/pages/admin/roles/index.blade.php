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
                    @can('manage roles')
                        <div class="panel-action">
                            <a href="{{ route('roles.create') }}" class="btn btn-success btn-sm">
                                <i class="fa fa-plus"></i> Tambah Role
                            </a>
                        </div>
                    @else
                        <p class="text-muted">Anda tidak memiliki izin untuk menambah role.</p>
                    @endcan

                    @can('manage roles')
                        <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $role)
                                    <tr>
                                        <td>{{ $loop->iteration  }}</td>
                                        <td>{{ $role->name }}</td>
                                        <td>
                                            @can('manage roles')
                                                <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary btn-xs">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST" style="display: inline;"
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
                        <p class="text-danger">Anda tidak memiliki izin untuk melihat daftar role.</p>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection


