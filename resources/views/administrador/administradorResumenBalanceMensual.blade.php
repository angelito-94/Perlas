@extends('home')

@section('entiquetasBody')
<li><a href="">Resumen</a></li>
<li><a href="">Resumen Mensual</a></li>
@endsection


@section('contenido')
<div class="col-lg-12 ">
    <div class="card center-block">
        <div class="card-header">
            <strong class="card-title">Resumen de Estados Financieros Mensuales</strong>
        </div>
        <div class="card-body card-block">
            <form action="{{route('excelmensual')}}" method="POST" enctype="multipart/form-data" class="form-horizontal">
                {{ csrf_field() }}
                <div class="form-group row ">
                    <div class="col-5 col-md-4">
                        <select name="anio" id="anio" class="form-control-sm form-control" required>
                            <option value=0>Seleccione el año...</option>
                            @foreach ($Anios as $Anio)
                            <option value="{{$Anio['codanio']}}">{{$Anio['nomanio']}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row" >
                    <div class="col-5 col-md-4">
                        <select name="mes" id="mes" class="form-control-sm form-control" required style="display:none;">

                        </select>
                    </div>
                </div>

                
                <div class="box-body" id="balance">
                    <table id="tabledata" class="table table-hover table-condensed table-striped table-bordered" style="font-size:0.75em;">
                    </table>
                    <br>
                </div>



            </form>
        </div>
    </div>
</div>


<!-- JavaScript-->
<script type="text/javascript">
    $('#anio').on('change', function(e) {
        var codanio = e.target.value;
        $.get('/Meses/' + codanio, function(data) {
            console.log(data);
            $('#mes').show();
            $('#mes').empty();
            $('#mes').append('<option value="">--Seleccione el mes</option>');
            $.each(data, function(index, datos) {
                $('#mes').append('<option value="' + datos.codaniomes + '">' + datos.nommes + '</option>');
            })
        });
    });
</script>

<script type="text/javascript">
    $('#mes').on('change', function(e) {
        var codaniomes = e.target.value;
        var codigo = "";
        $('#tabledata').empty();
        $.get('/conosinresumenmensual/' + codaniomes, function(data) {
            var i = 1;
            console.log(data);
            if (data.length > 0) {
                $.each(data, function(index, datos) {
                    $('#tabledata').append('<tr><th>Cuenta</th><th>Año: ' + datos.nomanio + ' Mes: ' + datos.nommes + '</th></tr>' +
                        '<tr><th>Cartera por vencer</th><td>' + datos.Carteraporvencer + '</td></tr>' +
                        '<tr><th>Cartera que no devenga intereses</th><td>' + datos.Carteraquenodevengaintereses + '</td></tr>' +
                        '<tr><th>Cartera vencida</th><td>' + datos.Carteravencida + '</td></tr>' +
                        '<tr><th>Cartera bruta</th><td>' + datos.Carterabruta + '</td></tr>' +
                        '<tr><th>Proviciones</th><td>' + datos.Proviciones + '</td></tr>' +
                        '<tr><th>Cartera neta</th><td>' + datos.Carteraneta + '</td></tr>' +
                        '<tr><th>Bienes en dacion de pago</th><td>' + datos.Bienesendaciondepago + '</td></tr>' +
                        '<tr><th>Otros activos</th><td>' + datos.Otrosactivos + '</td></tr>' +
                        '<tr><th>Total activos improductivos</th><td>' + datos.Totalactivosimproductivos + '</td></tr>' +
                        '<tr><th>Depositos de socios</th><td>' + datos.Depositosdesocios + '</td></tr>' +
                        '<tr><th>Obligaciones financieras</th><td>' + datos.Obligacionesfinancieras + '</td></tr>' +
                        '<tr><th>Pasivos con costo</th><td>' + datos.Pasivosconcosto + '</td></tr>' +
                        '<tr><th>Reservas totales</th><td>' + datos.Reservastotales + '</td></tr>' +
                        '<tr><th>Depositos a la vista</th><td>' + datos.Depositosalavista + '</td></tr>' +
                        '<tr><th>Depositos a plazo</th><td></td></tr>' +
                        '<tr><th>De 1 a 30 dias</th><td>' + datos.de1a30dias + '</td></tr>' +
                        '<tr><th>De 31 a 90 dias</th><td>' + datos.de31a90dias + '</td></tr>' +
                        '<tr><th>Obligaciones financieras</th><td></td></tr>' +
                        '<tr><th>De 1 a 30 dias</th><td>' + datos.oblde1a30dias + '</td></tr>' +
                        '<tr><th>De 31 a 90 dias</th><td>' + datos.oblde31a90dias + '</td></tr>' +
                        '<tr><th>Total depositos corto plazo</th><td>' + datos.Totaldepositoscortoplazo + '</td></tr>' +
                        '<tr><th>Activo productivo</th><td>' + datos.Activoproductivo + '</td></tr>' +
                        '<tr><th>Pasivos sin costo</th><td>' + datos.Pasivossincosto + '</td></tr>');
                })
            } else {
                $('#tabledata').append('<tr><td><h3>No hay datos para este periodo</h3></td></tr>');
            }
        });
    });
</script>



@endsection