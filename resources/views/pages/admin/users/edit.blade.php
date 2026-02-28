@extends('layouts.admin.app')
@section('title', $page)
@push('styles')
@endpush
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Perbarui Data {{ $page }}</h3>
                </div>

                <div class="panel-body">
                    <div class="row m-t-20">
                        <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Nama <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required
                                        placeholder="Masukkan nama" value="{{ old('name', $user->name) }}" />
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" required
                                        placeholder="Masukkan email" value="{{ old('email', $user->email) }}" />
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Nomor Telepon</label>
                                    <input type="number" name="phone_number" class="form-control"
                                        placeholder="Masukkan nomor telepon" value="{{ old('phone_number', $user->phone_number) }}" />
                                    @error('phone_number')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Gambar</label>
                                    <input type="file" name="image" class="form-control" accept="image/*" />
                                    @if ($user->image)
                                        <img src="{{ getFile($user->image) }}" alt="{{ $user->name }}" style="max-width: 100px; margin-top: 10px;">
                                        <small class="text-muted">Gambar saat ini</small>
                                    @endif
                                    @error('image')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Password</label>
                                    <input type="password" name="password" id="pass2" class="form-control"
                                        placeholder="Masukkan password baru (kosongkan jika tidak ingin mengubah)" />
                                    @error('password')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Konfirmasi Password</label>
                                    <input type="password" name="password_confirmation" class="form-control"
                                        data-parsley-equalto="#pass2" placeholder="Konfirmasi password baru" />
                                </div>

                                <div class="form-group">
                                    <label>Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control" required>
                                        <option value="active"
                                            {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                                        <option value="inactive"
                                            {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                                    </select>
                                    @error('status')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Role <span class="text-danger">*</span></label>
                                    <select name="role" class="form-control" required>
                                        <option value="">-- Pilih Role --</option>
                                        @foreach (\Spatie\Permission\Models\Role::all() as $role)
                                            <option value="{{ $role->name }}"
                                                {{ old('role', $user->getRoleNames()->first()) === $role->name ? 'selected' : '' }}>
                                                {{ ucfirst($role->name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                        Simpan
                                    </button>
                                    <a href="{{ route('users.index') }}" class="btn btn-default waves-effect m-l-5">
                                        Batal
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
@endpush

