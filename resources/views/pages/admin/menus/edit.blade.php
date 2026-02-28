@extends('layouts.admin.app')
@section('title', $page)
@push('styles')
    <style>
        .form-group {
            margin-bottom: 15px;
        }
        .hidden {
            display: none;
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Perbarui {{ $page }}</h3>
                </div>
                <div class="panel-body">
                    <form action="{{ route('menu.update', $edit->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="name">Nama Menu</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $edit->name) }}" required>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="type_1">Tipe Menu</label>
                            <select name="type_1" id="type_1" class="form-control" required>
                                <option value="parent" {{ old('type_1', $edit->type_1) === 'parent' ? 'selected' : '' }}>Menu Utama</option>
                                <option value="submenu" {{ old('type_1', $edit->type_1) === 'submenu' ? 'selected' : '' }}>Submenu</option>
                            </select>
                            @error('type_1')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="type_2">Tipe Konten</label>
                            <select name="type_2" id="type_2" class="form-control" required>
                                <option value="page" {{ old('type_2', $edit->type_2) === 'page' ? 'selected' : '' }}>Halaman</option>
                                <option value="link" {{ old('type_2', $edit->type_2) === 'link' ? 'selected' : '' }}>Link</option>
                            </select>
                            @error('type_2')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group hidden" id="page_group">
                            <label for="page_id">Pilih Halaman</label>
                            <select name="page_id" id="page_id" class="form-control">
                                <option value="">Pilih Halaman</option>
                                @foreach($pages as $item)
                                    <option value="{{ $item->id }}" data-slug="{{ $item->slug }}" {{ (old('page_id') ?: (isset($selected_page_id) && $selected_page_id == $item->id ? 'selected' : '')) }}>
                                        {{ $item->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('page_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group" id="parent_id_group">
                            <label for="parent_id">Parent Menu</label>
                            <select name="parent_id" id="parent_id" class="form-control">
                                <option value="">Pilih Menu Utama</option>
                                @foreach($parent as $editItem)
                                    <option value="{{ $editItem->id }}" {{ old('parent_id', $edit->parent_id) == $editItem->id ? 'selected' : '' }}>
                                        {{ $editItem->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="slug">Slug/Link</label>
                            <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug', $edit->slug) }}" required>
                            @error('slug')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="active" {{ old('status', $edit->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $edit->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Perbarui</button>
                        <a href="{{ route('menu.index') }}" class="btn btn-default">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const type1Select = document.getElementById('type_1');
            const type2Select = document.getElementById('type_2');
            const parentIdGroup = document.getElementById('parent_id_group');
            const pageGroup = document.getElementById('page_group');
            const pageSelect = document.getElementById('page_id');
            const slugInput = document.getElementById('slug');

            function toggleParentId() {
                if (type1Select.value === 'submenu') {
                    parentIdGroup.classList.remove('hidden');
                } else {
                    parentIdGroup.classList.add('hidden');
                }
            }

            function togglePageAndSlug() {
                if (type2Select.value === 'page') {
                    pageGroup.classList.remove('hidden');
                    slugInput.setAttribute('placeholder', 'Masukkan atau pilih slug halaman');
                    const selectedPage = pageSelect.options[pageSelect.selectedIndex];
                    if (selectedPage && selectedPage.value) {
                        slugInput.value = selectedPage.getAttribute('data-slug') || '';
                    } else {
                        slugInput.value = '{{ old('slug', $edit->slug) }}';
                    }
                } else {
                    pageGroup.classList.add('hidden');
                    slugInput.removeAttribute('readonly'); 
                    slugInput.setAttribute('placeholder', 'Masukkan link');
                    slugInput.value = ''; 
                }
            }

            toggleParentId();
            togglePageAndSlug();

            type1Select.addEventListener('change', toggleParentId);
            type2Select.addEventListener('change', togglePageAndSlug);
            pageSelect.addEventListener('change', function () {
                const selectedPage = pageSelect.options[pageSelect.selectedIndex];
                if (type2Select.value === 'page' && selectedPage && selectedPage.value) {
                    slugInput.value = selectedPage.getAttribute('data-slug') || '';
                } else {
                    slugInput.value = '{{ old('slug', $edit->slug) }}';
                }
            });
        });
    </script>
@endpush

