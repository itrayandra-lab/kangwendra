@extends('layouts.admin.app')
@include('widget.admin.datatables')
@section('title', 'Jadwal Publish')

@push('styles')
<style>
    .switch { position: relative; display: inline-block; width: 48px; height: 24px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: #28a745; }
    input:checked + .slider:before { transform: translateX(24px); }
    .slider.round { border-radius: 34px; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Jadwal Publish Otomatis</h3>
            </div>
            <div class="panel-body">

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }} <button type="button" class="close" data-dismiss="alert">×</button>
                    </div>
                @endif

                <!-- Add New Schedule Form -->
                <div class="well well-sm mb-4" style="background:#f8f9fa; border:1px solid #e9ecef;">
                    <h5><i class="fa fa-plus"></i> Tambah Jadwal Baru</h5>
                    <form action="{{ route('schedules.store') }}" method="POST" class="form-inline">
                        @csrf
                        <div class="form-group mr-2">
                            <label class="sr-only">Jam</label>
                            <input type="time" name="time" class="form-control" required value="{{ old('time') }}">
                        </div>
                        <div class="form-group mr-2">
                            <label class="sr-only">Hari</label>
                            <select name="day_of_week" class="form-control">
                                <option value="">Setiap hari</option>
                                <option value="0">Minggu</option>
                                <option value="1">Senin</option>
                                <option value="2">Selasa</option>
                                <option value="3">Rabu</option>
                                <option value="4">Kamis</option>
                                <option value="5">Jumat</option>
                                <option value="6">Sabtu</option>
                            </select>
                        </div>
                        <div class="form-group mr-2">
                            <label class="sr-only">Max Posts</label>
                            <input type="number" name="max_posts" class="form-control" placeholder="Jumlah post" min="1" max="10" required value="{{ old('max_posts', 1) }}">
                        </div>
                        <div class="form-group mr-2">
                            <label class="checkbox-inline">
                                <input type="checkbox" name="is_active" checked> Aktif
                            </label>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fa fa-plus"></i> Tambah
                        </button>
                    </form>
                </div>

                <table id="schedules-table" class="table table-striped table-bordered dt-responsive nowrap" width="100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Jam</th>
                            <th>Hari</th>
                            <th>Max Post</th>
                            <th>Aktif</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Jadwal Publish</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="" method="POST" id="editForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Jam (WIB)</label>
                        <input type="time" name="time" id="edit_time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Hari</label>
                        <select name="day_of_week" id="edit_day_of_week" class="form-control">
                            <option value="">Setiap hari</option>
                            <option value="0">Minggu</option>
                            <option value="1">Senin</option>
                            <option value="2">Selasa</option>
                            <option value="3">Rabu</option>
                            <option value="4">Kamis</option>
                            <option value="5">Jumat</option>
                            <option value="6">Sabtu</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jumlah Post Maksimum</label>
                        <input type="number" name="max_posts" id="edit_max_posts" class="form-control" min="1" max="10" required>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="is_active" id="edit_is_active"> Aktif
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#schedules-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: '{{ route('schedules.index') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'time_display', name: 'time' },
            { data: 'day_display', name: 'day_of_week' },
            { data: 'max_posts', name: 'max_posts', orderable: false, searchable: false },
            { data: 'is_active', name: 'is_active', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });
});

function editSchedule(id, time, dayOfWeek, maxPosts, isActive) {
    $('#edit_id').val(id);
    $('#edit_time').val(time);
    $('#edit_day_of_week').val(dayOfWeek === '' ? '' : dayOfWeek);
    $('#edit_max_posts').val(maxPosts);
    $('#edit_is_active').prop('checked', isActive === 1);
    $('#editForm').attr('action', '{{ url('portal/schedules') }}/' + id);
    $('#editModal').modal('show');
}
</script>
@endpush
