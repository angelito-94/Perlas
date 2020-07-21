@extends('home')

@section('entiquetasBody')
<li><a href="">Información Personal</a></li>
@endsection

@section('contenido')

@foreach ($informacionPersonal as $inf)

<br>

<div class="col-lg-12 ">
    <div class="card center-block">
        <div class="card-header">
            <strong class="card-title">Balances</strong>
        </div>
        <div class="card-body card-block">
            <form role="form" method="post" action="{{route('UpdateUsuario')}}" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="form-group">
                    <input type="hidden" class="form-control" value="{{ $inf->id}}" name="id">
                </div>

                <div class="form-group">
                    <label>Nombre de Usuario</label>
                    <input type="text" class="form-control" value="{{ $inf -> name}}" name="name" required>
                </div>

                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" class="form-control" value="{{ $inf -> nombre}}" name="nombre" required>
                </div>

                <div class="form-group">
                    <label>Teléfono</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-phone"></i>
                        </div>
                        <input type="text" class="form-control" value="{{$inf -> telefono}}" name="telefono" onkeypress="return justNumbers(event);" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-at"></i>
                        </div>
                        <input type="email" class="form-control" placeholder="Correo Electrónico...." value="{{$inf -> email}}" name="email" required>
                    </div>
                </div>


                <div class="form-group">
                    <label for="exampleInputFile">Subir una Foto</label>
                    <input type="file" name="foto">
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    function justNumbers(e) {
        var keynum = window.event ? window.event.keyCode : e.which;
        if ((keynum == 8) || (keynum == 46))
            return true;

        return /\d/.test(String.fromCharCode(keynum));
    }
</script>


@endforeach



@endsection