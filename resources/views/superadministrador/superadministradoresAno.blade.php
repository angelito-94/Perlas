@extends('supereshome')

@section('entiquetasBody')
<li><a href="">Años</a></li>
<li><a href="">Registrar</a></li>
@endsection


@section('contenido')

<div class="col-lg-12 ">
    <div class="card center-block">
        <div class="card-header">
            <strong class="card-title">Años</strong>
        </div>
        <div class="card-body card-block">
            <form action="" method="POST" enctype="multipart/form-data" class="form-horizontal">
                {{ csrf_field() }}

                <table class="table table-hover table-condensed table-striped table-bordered" style="font-size:0.85em;">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Año</th>
                            <th class="text-center" width="150px">
                                <a href="#" class="create-modal btn btn-success btn-sm" data-toggle="modal" data-target="#create">
                                    <i class="fa fa-plus"></i>
                                </a>

                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($Anios as $admins)
                        <tr class="post{{$admins['codanio']}}">
                            <td>{{$admins['codanio']}}</td>
                            <td>{{$admins['nomanio']}}</td>
                            <td>
                                <a href="#" class="delete-modal btn btn-danger btn-sm" data-toggle="modal" data-target="#myModal" data-codanio="{{$admins['codanio']}}">
                                    <i class="fa fa-trash"></i>
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
                <form class="form-horizontal" role="form" method="POST" enctype="multipart/form-data" action="{{ route('addAno') }}">
                    {{ csrf_field() }}
                    <div class="form-group row add {{ $errors->has('nombre') ? ' has-error' : '' }}">
                        <label class="control-label col-sm-2" for="nombre">Nombre: </label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" id="nombre" name="nombre" min="2000" max="2040" placeholder="Año..." onkeypress="return justNumbers(event);" required>
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
        $('.modal-title').text('Guardar Año');
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
                    {{ csrf_field() }}
                    <div class="form-group row">
                        <label class="control-label col-sm-2" for="codanio">Código</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="codani" disabled>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="control-label col-sm-2" for="id">Nombre</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="nombr" disabled>
                        </div>
                    </div>
                </form>
                {{-- Form Delete Post --}}
                <div class="deleteContent">
                    ¿Esta seguro de eliminar este registro? <span class="title"></span>?
                    <span class="hidden id"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn actionBtn" data-dismiss="modal">
                    <span id="footer_action_button" class="fa"></span>
                </button>
                <button type="button" class="btn btn-warning" data-dismiss="modal">
                    <span class="fa fa"></span>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).on('click', '.delete-modal', function() {
        $('#footer_action_button').text(" Eliminar");
        $('#footer_action_button').removeClass('fa-check');
        $('#footer_action_button').addClass('fa-trash');
        $('.actionBtn').removeClass('btn-success');
        $('.actionBtn').addClass('btn-danger');
        $('.actionBtn').addClass('delete');
        $('.modal-title').text('Eliminar Año');
        $('#codani').text($(this).data('codanio'));
        $('.deleteContent').show();
        $('.form-horizontal').hide();
        $('.title').html($(this).data('title'));
        $('#myModal').modal('show');
    });

    $('.modal-footer').on('click', '.delete', function() {
        $.ajax({
            type: 'POST',
            url: "{{route('deleteAno')}}",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                'codanio': $('#codani').text()
            },
            beforeSend: function() {
                $('#loading-screen').show();

            },
            success: function(response) {
                $("#loading-screen").hide();
                $('.post' + $('#codanio').text()).remove();
                $('#alert').show();
                $('#alert').html(response.success);
                setTimeout(function() {
                    $('#alert').hide(100);
                }, 10000);
            },
            error: function() {
                $("#loading-screen").hide();
                $('#alertdanger').show();
                $('#alertdanger').html("Algo salió mal, Este año no se puede eliminar");
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