@extends('supereshome')

@section('entiquetasBody')
<li><a href="">Estados Financieros</a></li>
<li><a href="">Anual</a></li>
@endsection


@section('contenido')



<div class="col-lg-12 ">
    <div class="card center-block">
        <div class="card-header">
            <strong class="card-title">Estados Financieros</strong>
        </div>
        <div class="card-body card-block">
            <form action="{{route('superAdministradoresexcel')}}" method="POST" enctype="multipart/form-data" class="form-horizontal">
                {{ csrf_field() }}
                <div class="row form-group">
                    <div class="col-5 col-md-4">
                        <select name="anio" id="anio" class="form-control-sm form-control" required>
                            <option value=0>Seleccione el año...</option>
                            @foreach ($Anios as $Anio)
                            <option value="{{$Anio['codanio']}}">{{$Anio['nomanio']}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <br>

                <div id="actualizar">
                </div>

                <br>

                <div class="box-body" id="balance">
                    <table id="tabledata" class="table table-hover table-condensed table-striped table-bordered" style="font-size:0.75em;">
                    </table>
                    <br>
                    <div id="actualizar">
                    </div>
                </div>

                <div class="col-xs-12" id="subirbalance" name="subirbalance">
                </div>

            </form>
        </div>
    </div>
</div>


<!-- JavaScript-->
<script type="text/javascript">
    $('#anio').on('change', function(e) {
        if (e.target.value == 0) {
            $('#tabledata').empty();
            $('#subirbalance').empty();
            $('#actualizar').empty();
        } else {
            var codanio = e.target.value;
            var codigo = "";
            $('#tabledata').empty();
            $('#actualizar').empty();
            $('#subirbalance').empty();
            document.getElementById('balance').style.display = 'none';
            document.getElementById('subirbalance').style.display = 'none';
            $.get('/susconosinbalance/' + codanio, function(data) {
                var i = 1;
                console.log(data);  
                if (data.length > 0) {
                    document.getElementById('balance').style.display = 'block';
                    $.each(data, function(index, conosinbalance) {
                        codigo += '<tr><td>' + conosinbalance.codcontable + '</td><td>' + conosinbalance.nomcuenta + '</td><td style="width:18%;"><input type="text" style="font-size:1.1em; height:50%;" class="form-control" name="valorbalance' + i + '" id="valorbalance' + i + '" value="' + conosinbalance.valorbalance + '" ></td><td style="display:none;">' + conosinbalance.codanio + '<input type="hidden" class="form-control" name="codanio" id="codanio" value="' + conosinbalance.codanio + '"></td><td style="display:none;"><input type="hidden" class="form-control"  name="codcontable' + i + '" id="codcontable' + i + '" value="' + conosinbalance.codcontable + '"</td></tr>';
                        actualizar = '<div class="col text-center"><a href="#"><button type="button" class="btn btn-outline-primary btn-sm" onclick="opciones(1,' + conosinbalance.codanio + ')"><i class="fa fa-save"></i>&nbsp;Actualizar</button></a>&nbsp;&nbsp;&nbsp;<a href="#"><span class="btn btn-outline-primary btn-sm"><i class="fa fa-print"></i>&nbsp;Imprimir</span></a>&nbsp;&nbsp;&nbsp;<button type="button" id="eliminar" class="btn btn-outline-primary btn-sm" onclick="opciones(3,' + conosinbalance.codanio + ')"><i class="fa fa-trash"></i>&nbsp;Eliminar</button></div>';
                        i++;
                    })
                    $('#tabledata').append('<thead><tr><th>Codigo Cuenta</th><th>Nombre Cuenta</th><th>Valor</th><th style="display:none;"></th><th style="display:none;"></th></tr></thead><tbody>' + codigo + '</tbody>');
                    $("#tabledata").dataTable({
                        "language": {
                            "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
                        },
                        "bDestroy": true,
                        "aaSorting": [],
                        "processing": "Procesando...",
                        "lengthMenu": [
                            [10, 50, 100, 300,-1],
                            [10, 50, 100, 300,"Todo"]
                        ]
                    });
                    $('#actualizar').show();
                    $('#actualizar').append(actualizar);
                } else {
                    document.getElementById('subirbalance').style.display = 'block';
                    $('#subirbalance').append('<div class="row form-group"><div class="col-10 col-md-12"><label for="file-input" class=" form-control-label">Suba el Balance en formato .xls</label></div><div class="col-16 col-md-12"><input type="file" id="balance" name="balance" class="form-control-file" required></div></div><br><div class="input-group-btn"><button class="btn btn-outline-primary btn-sm" onclick="opciones(4,' + codanio + ')"><i class="fa fa-upload"></i>&nbsp;Subir</button></div>');
                }
            });
        }
    });
</script>


<script>
    function opciones(op, codanio) {
        if (op == 1) {
            var filas = [];
            var cont = 1;
            $('#tabledata tbody tr').each(function() {
                var codcontable = $(this).find('td').eq(0).html();
                var valorbalance = $(this).find('td input').eq(0).val();
                var anio = $(this).find('td').eq(3).text();
                
                var fila = {
                    'valorbalance': valorbalance,
                    'anio': anio,
                    'codcontable': codcontable
                };
                filas.push(fila);
                cont++;
            });
            console.log(filas);
            $.ajax({
                type: 'POST',
                url: "{{route('superAdministradoresactualizarbalance')}}",
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    'valores': filas,
                },


                beforeSend: function() {
                    $('#loading-screen').show();
                },

                success: function(response) {
                    $("#loading-screen").hide();
                    $('#alert').show();
                    $('#alert').html(response.success);
                    setTimeout(function() {
                        $('#alert').hide(100);
                    }, 10000);
                    $('#actualizar').hide();
                },

                complete: function() {
                    document.getElementById('balance').style.display = 'none';
                    document.getElementById('anio').value = "Seleccione el año...";
                }
            });
        } else if (op == 2) {

        } else if (op == 3) {
            $.ajax({
                url: "{{route('superAdministradoreseliminarbalance')}}",
                data: {
                    'anio': codanio
                },
                type: 'get',

                beforeSend: function() {
                    $('#loading-screen').show();
                },

                success: function(response) {
                    $("#loading-screen").hide();
                    $('#alert').show();
                    $('#alert').html(response.success);
                    setTimeout(function() {
                        $('#alert').hide(100);
                    }, 10000);
                },

                complete: function() {
                    document.getElementById('balance').style.display = 'none';
                    document.getElementById('anio').value = "Seleccione el año...";
                },

                error: function() {
                    $("#loading-screen").hide();
                    $('#alertdanger').show();
                    $('#alertdanger').html("Algo salió mal, Asegurese de llenar todos los campos");
                    setTimeout(function() {
                        $('#alertdanger').hide(100);
                    }, 10000);
                },
            });
        } else if (op == 4) {
            $.ajax({
                url: "{{route('superAdministradoresexcel')}}",
                data: {
                    'anio': codanio
                },
                type: 'get',

                beforeSend: function() {
                    $('#loading-screen').show();
                },

                success: function(response) {
                    $("#loading-screen").hide();
                },

                complete: function(response) {
                    document.getElementById('balance').style.display = 'none';
                },
                error: function() {
                    $("#loading-screen").hide();
                },
            });
        }
    }
</script>


@endsection