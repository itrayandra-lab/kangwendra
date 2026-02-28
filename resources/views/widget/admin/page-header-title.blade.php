<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <div class="page-header-title">
            <h4 class="pull-left page-title">{{ ucwords(str_replace('-', ' ', $page ?? Request::segment(1))) }}</h4>
            <ol class="breadcrumb pull-right">
                <li><a href="{{ url('/portal/home') }}"><i class="fas fa fa-home"></i></a></li>
                @for($i = 2; $i <= count(Request::segments()); $i++)
                    <li {{ $i === count(Request::segments()) ? 'class=active' : '' }}>
                        @if($i === count(Request::segments()))
                            {{ ucwords(str_replace('-', ' ', Request::segment($i))) }}
                        @else
                            <a href="{{ url(implode('/', array_slice(Request::segments(), 0, $i + 1))) }}">
                                {{ ucwords(str_replace('-', ' ', Request::segment($i))) }}
                            </a>
                        @endif
                    </li>
                @endfor
            </ol>
            <div class="clearfix"></div>
        </div>
    </div>
</div>


