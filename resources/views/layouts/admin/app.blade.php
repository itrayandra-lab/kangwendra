<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>@yield('title', $meta->meta_title)</title>

    <!-- Meta Tags -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="description" content="{{ $meta->meta_description }}" />
    <meta name="keywords" content="{{ $meta->meta_keywords }}" />
    <meta name="author" content="{{ $meta->web_name }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Open Graph Meta -->
    <meta property="og:title" content="{{ $meta->meta_title }}">
    <meta property="og:description" content="{{ $meta->meta_description }}">
    <meta property="og:image" content="{{ getFile($meta->og_image) }}">
    <meta property="og:url" content="{{ $meta->domain }}">
    <meta property="og:type" content="website">

    <!-- Favicon & Logo -->
    <link rel="shortcut icon" href="{{ getFile($meta->favicon) }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ getFile($meta->logo) }}">

    <!-- Stylesheets -->
    <link href="{{ asset('dist/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('dist/css/icons.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('dist/css/style.css') }}" rel="stylesheet" type="text/css">

    <!-- DataTables -->
    <link href="{{ asset('dist/plugins/datatables/jquery.dataTables.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('dist/plugins/datatables/responsive.bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('dist/plugins/datatables/dataTables.bootstrap.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Google Maps -->
    <script src="{{ $meta->google_maps }}"></script>

    <!-- Vite Assets -->
    @vite(['resources/css/styles.css', 'resources/js/app.js', 'resources/js/script.js'])

    <!-- Additional Styles -->
    @stack('styles')
    
    <style>
        .sidebar-collapsed .left.side-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }
        .sidebar-collapsed .content-page {
            margin-left: 0 !important;
            width: 100% !important;
            transition: margin-left 0.3s ease, width 0.3s ease;
        }
        .sidebar-collapsed .topbar {
            left: 0 !important;
            transition: left 0.3s ease;
        }
        
        body:not(.sidebar-collapsed) .left.side-menu {
            transform: translateX(0);
            transition: transform 0.3s ease;
        }
        body:not(.sidebar-collapsed) .content-page {
            transition: margin-left 0.3s ease, width 0.3s ease;
        }
        body:not(.sidebar-collapsed) .topbar {
            transition: left 0.3s ease;
        }
        
        body.sidebar-collapsed#wrapper.enlarged .left.side-menu {
            width: 0 !important;
            transform: translateX(-100%);
        }
        
        .sidebar-collapsed .container {
            max-width: none !important;
            padding-left: 15px;
            padding-right: 15px;
        }
        
        .button-menu-mobile.open-left {
            display: block !important;
            visibility: visible !important;
            z-index: 1000;
        }
        
        .left.side-menu {
            transition: transform 0.3s ease, width 0.3s ease;
        }
        .content-page {
            transition: margin-left 0.3s ease, width 0.3s ease;
        }
        .topbar {
            transition: left 0.3s ease;
        }
    </style>
</head>


<body class="fixed-left @if(in_array(Route::currentRouteName(), ['posts.create', 'posts.edit'])) sidebar-collapsed @endif">

    <div id="wrapper">
        @include('widget.admin.topbar')
        @include('widget.admin.sidebar')
        <div class="content-page">
            <div class="content">
                <div class="container">
                    @include('widget.admin.page-header-title')
                    @yield('content')
                </div>
            </div>
           
        </div>
    </div>

    <!-- jQuery  -->
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
    <script src="{{ asset('dist/plugins/jquery-sparkline/jquery.sparkline.min.js') }}"></script>
    <script src="{{ asset('dist/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('dist/plugins/datatables/dataTables.bootstrap.js') }}"></script>
    <script src="{{ asset('dist/plugins/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('dist/plugins/datatables/responsive.bootstrap.min.js') }}"></script>
    <script src="{{ asset('dist/pages/dashborad.js') }}"></script>
    <script src="{{ asset('dist/plugins/parsleyjs/parsley.min.js') }}"></script>

    <script src="{{ asset('dist/js/app.js') }}"></script>

    <script>
        $(document).ready(function() {
            var currentRoute = '{{ Route::currentRouteName() }}';
            var isPostsPage = ['posts.create', 'posts.edit'].includes(currentRoute);
            
            if (isPostsPage) {
                $('body').addClass('sidebar-collapsed');
                
                $('.button-menu-mobile.open-left').off('click').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    $('body').toggleClass('sidebar-collapsed');
                    
                    $('#wrapper').removeClass('enlarged');
                    
                    var isCollapsed = $('body').hasClass('sidebar-collapsed');
                    console.log('Sidebar toggled, collapsed:', isCollapsed);
                });
                $(document).off('click.sidebar-menu').on('click.sidebar-menu', '.button-menu-mobile', function(e) {
                    if (isPostsPage) {
                        e.stopPropagation();
                        $(this).trigger('click');
                    }
                });
                
            } else {
                $('body').removeClass('sidebar-collapsed');
                
                $('.button-menu-mobile.open-left').off('click.posts-override');
            }
            
        });
    </script>

    @include('components.confrm_session_swal')
    @stack('scripts')

</body>

</html>


