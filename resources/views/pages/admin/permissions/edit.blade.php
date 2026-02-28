@extends('layouts.admin.app')
@section('title', $page)
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ $page }}</h3>
                </div>
                <div class="panel-body">
                    <form action="{{ route('permissions.update', $permission->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="name">Nama Permission</label>
                            <input type="text" name="name" class="form-control" value="{{ $permission->name }}" required>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-success">Update</button>
                        <a href="{{ route('permissions.index') }}" class="btn btn-default">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

