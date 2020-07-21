@extends('layouts.app')

@section('content')
<div class="site-blocks-cover" id="home-section">
    <div class="img-wrap">
        <div class="owl-carousel slide-one-item hero-slider">
            <div class="slide">
                <img src="{{asset('images/finanzas1.jpg')}}" alt="Free Website Template by Free-Template.co">
            </div>
            <div class="slide">
                <img src="{{asset('images/finanzas2.jpg')}}" alt="Free Website Template by Free-Template.co">
            </div>
            <div class="slide">
                <img src="{{asset('images/finanzas3.jpg')}}" alt="Free Website Template by Free-Template.co">
            </div>
            <div class="slide">
                <img src="{{asset('images/finanzas4.jpg')}}" alt="Free Website Template by Free-Template.co">
            </div>
        </div>
    </div>
    <div class="container ">
        <div class="row" style="width:105%;">
            <div class="col-md-6 ml-auto align-self-center text-dark" >
                <div class="intro">
                    <div class="panel panel-default">
                        <h3>Login</h3>
                        <br>
                        <div class="panel-body">
                            <form class="form-horizontal" method="POST" action="{{ route('login') }}">
                                {{ csrf_field() }}
                                <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                    <div class="col-md-6">
                                        <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Username..." required autofocus>

                                        @if ($errors->has('name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                    <div class="col-md-6">
                                        <input id="password" type="password" class="form-control" name="password" placeholder="Contraseña..." required>
                                        @if ($errors->has('password'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Recordar Contraseña
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-8 col-md-offset-4">
                                        <button type="submit" class="btn btn-primary">Login</button>
                                        <a class="btn btn-link" href="{{ route('password.request') }}">
                                            Olvidaste tu Contraseña?
                                        </a>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection