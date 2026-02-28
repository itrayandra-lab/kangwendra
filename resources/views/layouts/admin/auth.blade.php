<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>@yield('title', $meta->web_name . ' - Portal')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta content="Rizki Putra Ramadhan" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="shortcut icon" href="{{ getFile($meta->favicon) }}">
    <link href="{{ asset('dist/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('dist/css/icons.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('dist/css/style.css') }}" rel="stylesheet" type="text/css">
    @vite('resources/css/styles.css')
    @stack('styles')
</head>
<body>
    <div class="wrapper-page">
        <div class="panel panel-color panel-primary panel-pages">
            @yield('content')
        </div>
    </div>
    @stack('scripts')
    <script src="{{ asset('dist/js/jquery.min.js') }}"></script>
    <script src="{{ asset('dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('dist/js/modernizr.min.js') }}"></script>
    <script src="{{ asset('dist/js/detect.js') }}"></script>
    <script src="{{ asset('dist/js/fastclick.js') }}"></script>
    <script src="{{ asset('dist/js/jquery.slimscroll.js') }}"></script>
    <script src="{{ asset('dist/js/jquery.blockUI.js') }}"></script>
    <script src="{{ asset('dist/js/waves.js') }}"></script>
    <script src="{{ asset('dist/js/wow.min.js') }}"></script>
    <script src="{{ asset('dist/js/jquery.nicescroll.js') }}"></script>
    <script src="{{ asset('dist/js/jquery.scrollTo.min.js') }}"></script>
    <script src="{{ asset('dist/js/app.js') }}"></script>
    <script type="text/javascript" src="{{ asset('dist/plugins/parsleyjs/parsley.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('form').parsley();
        });
    </script>
</body>
</html>


