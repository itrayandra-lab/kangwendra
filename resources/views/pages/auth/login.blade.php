@extends('layouts.admin.auth')
@section('content')
    <div class="panel-body">
        <h3 class="text-center m-t-0 m-b-30">
            <span class=""><img src="{{ getFile($meta->logo) }}" alt="logo" height="50"></span>
        </h3>
        <h4 class="text-muted text-center m-t-0"><b>Sign In Portal</b></h4>
        @include('components.alert-basic')
        <form class="form-horizontal m-t-20" action="{{ route('portal.login.submit') }}" method="POST">
            @csrf
            <div class="form-group">
                <div class="col-xs-12">
                    <input class="form-control" type="email" name="email" required="" placeholder="Email">
                </div>
            </div>

            <div class="form-group">
                <div class="col-xs-12">
                    <input class="form-control" type="password" name="password" required="" placeholder="Password">
                </div>
            </div>

            <div class="form-group">
                <div class="col-xs-12">
                    <div class="checkbox checkbox-primary">
                        <input id="checkbox-signup" name="remember-me" type="checkbox">
                        <label for="checkbox-signup">
                            Remember me
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group text-center m-t-20">
                <div class="col-xs-12">
                    <button class="btn btn-primary btn-block waves-effect waves-light" type="submit">
                        <i class="fa fa-lock"></i> Log In
                    </button>
                </div>
            </div>


            <div class="form-group m-t-30 m-b-0">
                <div class="col-sm-7">
                    <a href="/" class="text-muted"><i class="fa fa-long-arrow-left m-r-5"></i> Back To Landing
                        Page</a>
                </div>

            </div>
        </form>
    </div>
@endsection


