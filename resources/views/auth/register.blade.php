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
    <div class="container">
        <div class="row" style="width:105%;">
            <div class="col-md-6 ml-auto align-self-center text-dark">
                <div class="intro">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3>Registro</h3>
                        </div>
                        <div class="panel-body">
                            <br>
                            <form class="form-horizontal" method="POST" action="{{ route('register') }}">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <div class="col-md-6">
                                        <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Username..." required autofocus>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-6">
                                        <input id="nombre" type="text" class="form-control" name="nombre" value="{{ old('nombre') }}" placeholder="Nombre de la Empresa..." required autofocus>

                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-6">
                                        <input id="telefono" type="text" class="form-control" name="telefono" value="{{ old('telefono') }}" placeholder="Teléfono..." required autofocus>
                                    </div>
                                </div>

                                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                    <div class="col-md-6">
                                        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="E-Mail..." required>
                                        @if ($errors->has('email'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                    <div class="col-md-6">
                                        <input id="password" type="password" class="form-control" name="password" placeholder="Contraseña..." required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="tipo" class="control-label col-sm-2">Tipo</label>
                                    <div class="col-md-6">
                                        <select class="form-control select2" style="width: 100%;" name="tipuser" id="tipuser">
                                            <option value="NINGUNO">--Escoja el tipo --</option>
                                            <option value="ADMINISTRADOR">ADMINISTRADOR</option>
                                            <option value="USUARIO">USUARIO</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <button type="submit" class="btn btn-primary">
                                            Registrate
                                        </button>
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