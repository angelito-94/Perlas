@extends('home')

@section('entiquetasBody')
<li><a href="">Indicadores</a></li>
<li><a href="">Anual</a></li>
@endsection


@section('contenido')

<div class="col-lg-12 ">
    <div class="card center-block">

        <div class="card-header">
            <strong class="card-title">Indicadores Anuales</strong>
        </div>

        <div class="card-body card-block">
            {{ csrf_field() }}
            <div class="col-xs-4">
                <div class="form-group">
                    <select class="form-control select2" style="width: 100%;" name="periodos" id="periodos">
                        @foreach ($Anios as $anio)
                        <option value="{{ $anio['codanio']}}">{{$anio['nomanio']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body card-block">
            <table id="tabledata" class="table table-hover table-condensed table-bordered" style="font-size:0.75em;">
            </table>
        </div>

    </div>
</div>

<div class="modal fade" id="modalVerIndicador" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Información</h4>
            </div>
            <div class="modal-body">
                <div class="col-xs-12" name="indicador" id="indicador">
                </div>

                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $('#periodos').on('change', function(e) {
        var codanio = e.target.value;
        var porcentual = 0;
        var valores = "";
        if ($(this).children('option:first-child').is(':selected')) {
            $('#tabledata').empty();
            $('#tabledata').append("<tr><th><h4>No hay registros anteriores a este, con cual comparar!</h4></th></tr>");
        } else {
            $.get('/indicadores/' + codanio, function(data) {
                $(document).ready(function() {
                    console.log(data);
                    $('#tabledata').empty();
                    $.each(data, function(index, periodosobj) {
                        $('#tabledata').append("<tr><th>Código</th><th>Indicador</th><th>Formula</th><th style='width:10%;'>Valor " + periodosobj.nomanio2 + "</th><th style='width:10%;'>Valor " + periodosobj.nomanio + "</th><th style='width:10%;'>Valoración Porcentual Año " + periodosobj.nomanio + "</th><th>Información</th></tr>");
                        return false;
                    });
                    $.each(data, function(index, periodosobj) {
                        if ((periodosobj.valorindicador2 == 0) || (periodosobj.valorindicador2 > 1000000)) {
                            var porcentual = "No hay valor previo";
                            periodosobj.valorindicador2 = "No hay valor previo"
                        } else {
                            porcentual = Number(((periodosobj.valorindicador / periodosobj.valorindicador2) - 1) * 100).toFixed(2);
                        }

                        if (periodosobj.codindicador === 'P1') {
                            if (porcentual < 100) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'P2') {
                            if (porcentual < 100) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'P3') {
                            if (porcentual < 111) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'P4') {
                            if (porcentual < 15) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'E1') {
                            if ((porcentual < 70) && (porcentual > 80)) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'E2') {
                            if (porcentual > 16) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'E3') {
                            if ((porcentual < 70) && (porcentual > 80)) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'E4') {
                            if (porcentual > 2) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'E5') {
                            if (porcentual > 20) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'E6') {
                            if (porcentual < 10) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'R1') {
                            if (porcentual < 10) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'R2') {
                            if (porcentual < 10) {
                                color = 'class="text-success"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'R3') {
                            if (porcentual < 18) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'R4') {
                            if (porcentual < 5) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'R5') {
                            if (porcentual > 2) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'R6') {
                            if (porcentual < 5) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'R7') {
                            if (porcentual < 10) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'R8') {
                            if (porcentual > 5) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'R9') {
                            if (porcentual < 300) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'R10') {
                            if (porcentual > 100) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'R11') {
                            if (porcentual > 5) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'R12') {
                            if (porcentual > 3) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'R13') {
                            if (porcentual > 2) {
                                color = 'class="text-danger"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'R14') {
                            if (porcentual < 10) {
                                color = 'class="text-success"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'R15') {
                            if (porcentual < 10) {
                                color = 'class="text-success"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'R16') {
                            if (porcentual < 12) {
                                color = 'class="text-success"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'L1') {
                            if ((porcentual < 15) && (porcentual > 20)) {
                                color = 'class="text-success"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'L2') {
                            if (porcentual < 20) {
                                color = 'class="text-success"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'A1') {
                            if (porcentual > 3) {
                                color = 'class="text-success"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'A2') {
                            if (porcentual > 5) {
                                color = 'class="text-success"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'A3') {
                            if (porcentual > 5) {
                                color = 'class="text-success"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else if (periodosobj.codindicador === 'A4') {
                            if (porcentual < 200) {
                                color = 'class="text-success"';
                            } else {
                                color = 'class="text-success"';
                            }
                        } else {}

                        $('#tabledata').append('<tr><td>' + periodosobj.codindicador + '</td><td>' + periodosobj.nomindicador + '</td><td>' + periodosobj.forindicador + '</td><td>' + periodosobj.valorindicador2 + '%</td><td>' + periodosobj.valorindicador + '%</td><td ' + color + '>' + porcentual + '%</td><td><button type="button" id="abrir" name="abrir" class="btn btn-bg" data-toggle="modal" data-target="#modalVerIndicador" onclick="agregaform(\'' + periodosobj.codindicador + '\',\'' + periodosobj.nomindicador + '\',\'' + periodosobj.forindicador + '\',\'' + periodosobj.calindicador + '\',\'' + periodosobj.calindicador2 + '\',' + periodosobj.valorindicador + ',' + periodosobj.valorindicador2 + ',' + porcentual + ',\'' + periodosobj.metindicador + '\',\'' + periodosobj.nomanio + '\',\'' + periodosobj.nomanio2 + '\')"><i class="fa fa-eye" aria-hidden="true"></i></button></td></tr>');
                    })
                });
            });
        }
    });
</script>

<script type="text/javascript">
    function agregaform(codindicador, nomindicador, forindicador, calindicador, calindicador2, valorindicador, valorindicador2, porcentual, metindicador, nomanio, nomanio2) {
        $('#indicador').empty();
        $('#columnchart_values').empty();
        $('#indicador').append('<table class="table table-hover table-condensed table-bordered" style="font-size:0.75em;"><tr><th>Indicador</th><th>Fórmula</th><th>Cálculo ' + nomanio2 + '</th><th>Cálculo ' + nomanio + '</th><th>Valor ' + nomanio2 + '</th><th>Valor ' + nomanio + '</th><th>Variación Porcentual ' + nomanio + '</th><th>Meta</th></tr><tr><td>' + codindicador + '</td><td>' + forindicador + '</td><td>' + calindicador2 + '</td><td>' + calindicador + '</td><td>' + valorindicador2 + '</td><td>' + valorindicador + '</td><td>' + porcentual + '</td><td>' + metindicador + '</td></tr></table><center><div class="col-xs-12"><div id="columnchart_values"></div></div></center>');
        google.charts.load("current", {
            packages: ['corechart']
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ["Element", "Valor", {
                    role: "style"
                }],
                [nomanio2, valorindicador2, "#2FDD69"],
                [nomanio, valorindicador, "#26CAFF"],
                [nomanio, porcentual, "#FF8C26"]
            ]);

            var view = new google.visualization.DataView(data);
            view.setColumns([0, 1,
                {
                    calc: "stringify",
                    sourceColumn: 1,
                    type: "string",
                    role: "annotation"
                },
                2
            ]);

            var options = {
                title: nomindicador,
                width: 500,
                height: 400,
                bar: {
                    groupWidth: "150%"
                },
                legend: {
                    position: "none"
                },
            };
            var chart = new google.visualization.ColumnChart(document.getElementById("columnchart_values"));
            chart.draw(view, options);
        }
    }
</script>

<script type="text/javascript">


</script>




@endsection