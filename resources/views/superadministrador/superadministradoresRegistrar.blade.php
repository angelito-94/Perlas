@extends('supereshome')

@section('entiquetasBody')
<li><a href="">Usuarios</a></li>
<li><a href="">Registrar</a></li>
@endsection


@section('contenido')

<div class="col-lg-12 ">
    <div class="card center-block">
        <div class="card-header">
            <strong class="card-title">Registros</strong>
        </div>
        <div class="card-body card-block">
            <form action="" method="POST" enctype="multipart/form-data" class="form-horizontal">
                {{ csrf_field() }}

                <table class="table table-hover table-condensed table-striped table-bordered" style="font-size:0.75em;">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Username</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            <th class="text-center" width="150px">
                                <a href="#" class="create-modal btn btn-success btn-sm" data-toggle="modal" data-target="#create">
                                    <i class="fa fa-plus"></i>
                                </a>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($Users as $admins)
                        <tr class="post{{$admins['id']}}">
                            <td>{{$admins['id']}}</td>
                            <td>{{$admins['name']}}</td>
                            <td>{{$admins['nombre']}}</td>
                            <td>{{$admins['telefono']}}</td>
                            <td>{{$admins['email']}}</td>
                            <td>{{$admins['tipuser']}}</td>
                            <td>
                                <a href="#" class="edit-modal btn btn-warning btn-sm" data-toggle="modal" data-target="#myModal" data-id="{{$admins['id']}}" data-name="{{$admins['name']}}" data-nombre="{{$admins['nombre']}}" data-telefono="{{$admins['telefono']}}" data-email="{{$admins['email']}}" data-tipuser="{{$admins['tipuser']}}">
                                    <i class="fa fa-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
        </div>
    </div>
</div>

<div id="create" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" role="form" method="POST" enctype="multipart/form-data" action="{{ route('addUsuario') }}">
                    {{ csrf_field() }}
                    <div class="form-group row add {{ $errors->has('cedula') ? ' has-error' : '' }}">
                        <label class="control-label col-sm-2" for="cedula">Username: </label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="name" name="name" placeholder="Nombre de usuario....." required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="control-label col-sm-2" for="name">Nombre: </label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre...." required>
                        </div>
                    </div>
                    <div class="form-group row {{ $errors->has('password') ? ' has-error' : '' }}">
                        <label class="control-label col-sm-2" for="telefono">Contraseña: </label>
                        <div class="col-md-10">
                            <input id="password" type="password" class="form-control" name="password" placeholder="Contraseña..." required>
                        </div>
                    </div>
                    <div class="form-group row {{ $errors->has('telefono') ? ' has-error' : '' }}">
                        <label class="control-label col-sm-2" for="telefono">Teléfono: </label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Teléfono..." onkeypress="return justNumbers(event);" required>
                        </div>
                    </div>
                    <div class="form-group row {{ $errors->has('email') ? ' has-error' : '' }}">
                        <label class="control-label col-sm-2" for="email">Email: </label>
                        <div class="col-sm-10">
                            <input type="email" class="form-control" id="email" name="email" placeholder="Email....." required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="tipo" class="control-label col-sm-2">Tipo</label>
                        <div class="col-sm-10">
                            <select class="form-control select2" style="width: 100%;" name="tipuser" id="tipuser">
                                <option value="NINGUNO">--Escoja el tipo --</option>
                                <option value="ADMINISTRADOR">ADMINISTRADOR</option>
                                <option value="VICEDECANO">USUARIO</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-success" type="submit" id="add">
                            <span class="fa fa-plus"></span> Guardar
                        </button>
                        <button class="btn btn-warning" type="button" data-dismiss="modal">
                            <span class="fa fa-remove"></span> Cerrar
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            "destroy": true,
        });
    });

    $(document).on('click', '.create-modal', function() {
        $('#create').modal('show');
        $('.form-horizontal').show();
        $('.modal-title').text('Guardar Usuario');
    });
</script>

<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" role="modal" method="POST" enctype="multipart/form-data">
                    <div class="form-group row">
                        <label class="control-label col-sm-2" for="id">Código</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="id" disabled>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="control-label col-sm-2" for="id">Username</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="nam" disabled>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="control-label col-sm-2" for="title">Nombre</label>
                        <div class="col-sm-10">
                            <input type="name" class="form-control" id="nombr">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="control-label col-sm-2" for="body">Teléfono</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="telefon" onkeypress="return justNumbers(event);">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="control-label col-sm-2" for="email">Email</label>
                        <div class="col-sm-10">
                            <input type="email" class="form-control" id="emai">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="tipo" class="control-label col-sm-2">Tipo</label>
                        <div class="col-sm-10">
                            <select class="form-control select2" style="width: 100%;" for="tipo" id="tipuse">
                                <option value="NINGUNO">--Escoja el tipo --</option>
                                <option value="ADMINISTRADOR">ADMINISTRADOR</option>
                                <option value="USUARIO">USUARIO</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn actionBtn" data-dismiss="modal">
                    <span id="footer_action_button" class="glyphicon"></span>
                </button>
                <button type="button" class="btn btn-warning" data-dismiss="modal">
                    <span class="glyphicon glyphicon"></span>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).on('click', '.edit-modal', function() {
        $('#footer_action_button').text(" Actualizar");
        $('#footer_action_button').addClass('glyphicon-check');
        $('#footer_action_button').removeClass('glyphicon-trash');
        $('.actionBtn').addClass('btn-success');
        $('.actionBtn').removeClass('btn-danger');
        $('.actionBtn').addClass('edit');
        $('.modal-title').text('Editar Administrador');
        $('.deleteContent').hide();
        $('.form-horizontal').show();
        $('#id').val($(this).data('id'));
        $('#nam').val($(this).data('name'));
        $('#nombr').val($(this).data('nombre'));
        $('#telefon').val($(this).data('telefono'));
        $('#emai').val($(this).data('email'));
        $('#tipuse').val($(this).data('tipuser'));
        $('#myModal').modal('show');
    });

    $('.modal-footer').on('click', '.edit', function() {
        $.ajax({
            type: 'post',
            url: "{{route('editUsuario')}}",
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                'id': $("#id").val(),
                'name': $("#nam").val(),
                'nombre': $('#nombr').val(),
                'telefono': $('#telefon').val(),
                'email': $('#emai').val(),
                'tipuser': $('#tipuse').val()
            },
            beforeSend: function() {
                $('#loading-screen').show();
            },
            success: function(data) {
                $("#loading-screen").hide();
                $('#alert').show();
                $('#alert').html("Cambios generados correctamente");
                setTimeout(function() {
                    $('#alert').hide(100);
                }, 10000);
                $('.post' + data.id).replaceWith(" " +
                    "<tr class='post" + data.id + "'>" +
                    "<td>" + data.id + "</td>" +
                    "<td>" + data.name + "</td>" +
                    "<td>" + data.nombre + "</td>" +
                    "<td>" + data.telefono + "</td>" +
                    "<td>" + data.email + "</td>" +
                    "<td>" + data.tipuser + "</td>" +
                    "<td><a href='#' class='edit-modal btn btn-warning btn-sm' data-toggle='modal' data-target='#myModal' data-id='" + data.id + "' data-name='" + data.name + "' data-nombre='" + data.nombre + "'data-telefono='" + data.telefono + "'data-email='" + data.email + "'data-tipuser='" + data.tipuser + "'><i class='fa fa-pencil'></i></a></td>" +
                    "</tr>");
            },
            error: function() {
                $("#loading-screen").hide();
                $('#alertdanger').show();
                $('#alertdanger').html("Algo salió mal, Asegurese de llenar todos los campos, o actualice la página");
                setTimeout(function() {
                    $('#alertdanger').hide(100);
                }, 10000);
            },
        });
    });

    function justNumbers(e) {
        var keynum = window.event ? window.event.keyCode : e.which;
        if ((keynum == 8) || (keynum == 46))
            return true;

        return /\d/.test(String.fromCharCode(keynum));
    }
</script>

@endsection