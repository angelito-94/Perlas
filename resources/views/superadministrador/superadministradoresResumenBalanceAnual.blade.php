@extends('supereshome')

@section('entiquetasBody')
<li><a href="">Resumen</a></li>
<li><a href="">Resumen Anual</a></li>
@endsection


@section('contenido')

<div class="col-lg-12 ">
    <div class="card center-block">
        <div class="card-header">
            <strong class="card-title">Resumen de Estados Financieros</strong>
        </div>
        <div class="card-body card-block">
            <form action="" method="POST" enctype="multipart/form-data" class="form-horizontal">
                {{ csrf_field() }}
                <table class="table table-hover" style="font-size:0.75em;">
                    <tr> 
                        <th>CUENTA</th>
                        @foreach($Resultado as $anio)
                        <th>{{$anio['nomanio']}}</th>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Cartera por vencer</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['Carteraporvencer']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Cartera que no devenga intereses</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['Carteraquenodevengaintereses']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Cartera vencida</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['Carteravencida']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Cartera bruta</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['Carterabruta']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Proviciones</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['Proviciones']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Cartera neta</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['Carteraneta']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th>ACTIVOS IMPRODUCTIVOS</th>  
                        @foreach($Resultado as $res)
                        <td></td>
                        @endforeach                      
                    </tr>
                    <tr>
                        <td>Cuentas por cobrar</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['Cuentasporcobrar']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Bienes en dacion de pago</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['Bienesendaciondepago']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Activos fijos</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['Activosfijos']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Otros activos</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['Otrosactivos']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Total activos improductivos</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['Totalactivosimproductivos']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Depositos de socios</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['Depositosdesocios']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Obligaciones financieras</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['Obligacionesfinancieras']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Pasivos con costo</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['Pasivosconcosto']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Reservas totales</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['Reservastotales']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th>DEPÓSITOS A CORTO PLAZO</th>  
                        @foreach($Resultado as $res)
                        <td></td>
                        @endforeach                      
                    </tr>
                    <tr>
                        <td>Depositos a la vista</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['Depositosalavista']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th>Depósitos a plazo:</th>  
                        @foreach($Resultado as $res)
                        <td></td>
                        @endforeach                      
                    </tr>
                    <tr>
                        <td>De 1 a 30 dias</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['de1a30dias']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>De 31 a 90 dias</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['de31a90dias']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th>Obligaciones financieras:</th>  
                        @foreach($Resultado as $res)
                        <td></td>
                        @endforeach                      
                    </tr>
                    <tr>
                        <td>De 1 a 30 dias</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['oblde1a30dias']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>De 31 a 90 dias</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['oblde31a90dias']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Total depositos corto plazo</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['Totaldepositoscortoplazo']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Activo productivo</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['Activoproductivo']}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Pasivos sin costo</td>
                        @foreach($Resultado as $res)
                        <td>{{$res['Pasivossincosto']}}</td>
                        @endforeach
                    </tr>


                </table>
            </form>
        </div>
    </div>
</div>


@endsection