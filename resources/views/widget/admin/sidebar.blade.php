<div class="left side-menu">
    <div class="sidebar-inner slimscrollleft">
        <div class="user-details">
            <div class="text-center">
                <img src="{{ Auth::user()->image ? getFile(Auth::user()->image) : asset('dist/images/users/avatar-1.jpg') }}"
                    alt="" class="img-circle">
            </div>
            <div class="user-info">
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"
                        aria-expanded="false">{{ auth()->user()->name }}</a>
                </div>

                <p class="text-muted m-0"><i class="fa fa-lock text-success"></i>
                    {{ auth()->user()->getRoleNames()->first() }}</p>
            </div>
        </div>
        
        <div id="sidebar-menu">
            <ul>
                <!-- Menu utama untuk dashboard -->
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="waves-effect">
                        <i class="ti-home"></i><span>Dashboard</span>
                    </a>
                </li>

                <!-- Menu untuk mengelola identitas web -->
                @can('manage web identities')
                    <li>
                        <a href="{{ route('web-identity.index') }}" class="waves-effect {{ isActive($page, 'Identitas Web') }}">
                            <i class="fa fa-globe"></i><span>Meta Web</span>
                        </a>
                    </li>
                @endcan

                <!-- Menu untuk mengelola users -->
                @can('view users')
                    <li>
                        <a href="{{ route('users.index') }}" class="waves-effect {{ isActive($page, 'Users') }}">
                            <i class="ti-user"></i><span>Users</span>
                        </a>
                    </li>
                @endcan

                <!-- Menu untuk mengelola halaman -->
                @can('view pages')
                    <li>
                        <a href="{{ route('pages.index') }}" class="waves-effect {{ isActive($page, 'Halaman') }}">
                            <i class="ti-folder"></i><span>Pages</span>
                        </a>
                    </li>
                @endcan

                <!-- Menu untuk mengelola Menu -->
                @can('view menu')
                    <li>
                        <a href="{{ route('menu.index') }}" class="waves-effect {{ isActive($page, 'Menu') }}">
                            <i class="ti-menu"></i><span>Menu</span>
                        </a>
                    </li>
                @endcan

                <!-- Menu dropdown untuk post -->
                @if (auth()->user()->hasAnyPermission(['view categories', 'view tags', 'view posts']))
                    <li class="has_sub">
                        <a href="javascript:void(0);" class="waves-effect {{ isActive($page, ['Kategori', 'Tags', 'posts']) }}">
                            <i class="fa fa-newspaper-o"></i> <span>Berita</span> <span class="pull-right"><i class="mdi mdi-plus"></i></span>
                        </a>
                        <ul class="list-unstyled">
                            @can('view categories')
                                <li>
                                    <a class="{{ isActive($page, 'Kategori') }}" href="{{ route('categories.index') }}">
                                        Kategori
                                    </a>
                                </li>
                            @endcan

                            @can('view tags')
                                <li>
                                    <a class="{{ isActive($page, 'Tags') }}" href="{{ route('tags.index') }}">
                                        Tags
                                    </a>
                                </li>
                            @endcan

                            @can('view posts')
                                <li>
                                    <a class="{{ isActive($page, 'posts') }}" href="{{ route('posts.index') }}">
                                        Postingan
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endif

                <!-- Menu dropdown untuk post -->
                @if (auth()->user()->hasAnyPermission(['view album', 'view video','view information', 'view ads']))
                    <li class="has_sub">
                        <a href="javascript:void(0);" class="waves-effect {{ isActive($page, ['Galery','Video', 'Informasi', 'Iklan']) }}">
                            <i class="fa fa-unlink"></i> <span>Postingan Lain</span> <span class="pull-right"><i class="mdi mdi-plus"></i></span>
                        </a>
                        <ul class="list-unstyled">
                            @can('view album')
                                <li>
                                    <a class="{{ isActive($page, 'Galery') }}" href="{{ route('album.index') }}">
                                        Galery
                                    </a>
                                </li>
                            @endcan
                            @can('view video')
                                <li>
                                    <a class="{{ isActive($page, 'Video') }}" href="{{ route('video.index') }}">
                                        Video
                                    </a>
                                </li>
                            @endcan
                            @can('view information')
                                <li>
                                    <a class="{{ isActive($page, 'Informasi') }}" href="{{ route('information.index') }}">
                                        Informasi
                                    </a>
                                </li>
                            @endcan
                            @can('view ads')
                                <li>
                                    <a class="{{ isActive($page, 'Iklan') }}" href="{{ route('ads.index') }}">
                                        Iklan
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endif

                <!-- Menu RSS Yahoo AI -->
                <li>
                    <a href="{{ route('rss.yahoo.index') }}" class="waves-effect {{ isActive($page, 'RSS Yahoo AI') }}">
                        <i class="fa fa-yahoo"></i><span>RSS Yahoo AI</span>
                    </a>
                </li>

                <!-- Menu Artikel Referensi & AI -->
                <li>
                    <a href="{{ route('ref-articles.index') }}" class="waves-effect {{ isActive($page, 'Artikel Referensi') }}">
                        <i class="fa fa-magic"></i><span>Scrape & AI Generate</span>
                    </a>
                </li>

                @if (auth()->user()->hasAnyPermission(['manage roles', 'manage permissions', 'view domain-share']))
                    <li class="has_sub">
                        <a href="javascript:void(0);" class="waves-effect {{ isActive($page, ['Roles', 'Permissions', 'Domain Share']) }}">
                            <i class="fa fa-lock"></i> <span>Hak Akses</span> <span class="pull-right"><i class="mdi mdi-plus"></i></span>
                        </a>
                        <ul class="list-unstyled">
                            @can('manage roles')
                                <li>
                                    <a class="{{ isActive($page, 'Roles') }}" href="{{ route('roles.index') }}">
                                        Roles
                                    </a>
                                </li>
                            @endcan

                            @can('manage permissions')
                                <li>
                                    <a class="{{ isActive($page, 'Permissions') }}" href="{{ route('permissions.index') }}">
                                        Permissions
                                    </a>
                                </li>
                            @endcan
                            
                            @can('view domain-share')
                                <li>
                                    <a class="{{ isActive($page, 'Domain Share') }}" href="{{ route('domain-share.index') }}">
                                        Domain Share
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endif

            </ul>
        </div>
        <div class="clearfix"></div>
    </div> <!-- end sidebarinner -->
</div>


