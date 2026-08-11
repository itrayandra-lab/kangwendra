<!-- DataTables assets (only include on pages that need them) -->
@push('styles')
    <link href="{{ asset('dist/plugins/datatables/jquery.dataTables.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('dist/plugins/datatables/responsive.bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('dist/plugins/datatables/dataTables.bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')
    <script src="{{ asset('dist/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('dist/plugins/datatables/dataTables.bootstrap.js') }}"></script>
    <script src="{{ asset('dist/plugins/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('dist/plugins/datatables/responsive.bootstrap.min.js') }}"></script>
@endpush
