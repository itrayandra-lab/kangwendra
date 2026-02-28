<!-- Top Bar Start -->
<div class="topbar">
    <!-- LOGO -->
    <div class="topbar-left">
        <div class="text-center">
            <a class="logo"><img src="{{ $meta->logo }}" height="50"></a>
            <a class="logo-sm"><img src="{{ $meta->favicon }}" height="36"></a>
        </div>
    </div>
    <!-- Button mobile view to collapse sidebar menu -->
    <div class="navbar navbar-default" role="navigation">
        <div class="container">
            <div class="">
                <div class="pull-left">
                    <button type="button" class="button-menu-mobile open-left waves-effect waves-light">
                        <i class="ion-navicon"></i>
                    </button>
                    <span class="clearfix"></span>
                </div>


                <ul class="nav navbar-nav navbar-right pull-right">
                    
                    <li class="hidden-xs">
                        <a href="#" id="btn-fullscreen" class="waves-effect waves-light"><i
                                class="fa fa-crosshairs"></i></a>
                    </li>
                    <li class="dropdown">
                        <a href="" class="dropdown-toggle profile waves-effect waves-light"
                            data-toggle="dropdown" aria-expanded="true"><img
                                src="{{ Auth::user()->image ? getFile(Auth::user()->image) : asset('dist/images/users/avatar-1.jpg') }}"
                                alt="user-img" class="img-circle">
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="{{ route('users.profil') }}">Profile</a></li>
                            <li class="divider"></li>
                            <li><a href="#" id="logout-link">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <!--/.nav-collapse -->
        </div>
    </div>
</div>


