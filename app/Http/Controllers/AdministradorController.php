<?php

namespace App\Http\Controllers;

use App\Anio;
use App\AnioMes;
use App\AnioSemestre;
use App\Balance;
use App\BalanceMensual;
use App\BalanceSemestral;
use App\Contactanos;
use App\Imports\BalanceExport;
use App\Imports\BalanceMensualImport;
use App\Imports\BalanceSemestralImport;
use App\Imports\EstadoResultadosImport;
use App\IndicadoresAnual;
use App\IndicadoresMensual;
use App\IndicadoresSemestral;
use App\Mes;
use App\Semestre;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\HttpFoundation\Response;

class AdministradorController extends Controller
{
    

    public function AdministradorPrincipal()
    {
        if (Auth::check()) {
            return view('administrador/administradorPrincipal');
        } else {
            return view('auth/login');
        }
    }

    public function InformacionUsuario()
    {
        if (Auth::check()) {
            $id = Auth::id();
            $informacionPersonal = User::where('users.id', $id)->get();
            if (!is_null($informacionPersonal)) {
                return view('administrador/administradorInformacion', compact('informacionPersonal'));
            } else {
                return response('Usuario no encontrado', 404);
            }
        } else {
            return view('auth/login');
        }
    }

    public function UpdateUsuario(Request $request)
    {
        if (Auth::check()) {
            $registro = User::find($request->id);
            $registro->name = $request->name;
            $registro->nombre = mb_strtoupper($request->nombre);
            $registro->telefono = $request->telefono;
            $registro->email = $request->email;
            $foto = $request->foto;
            if (!is_null($foto)) {
                $extension = $request->file('foto')->getClientOriginalExtension();
                $file_name = bcrypt($request->id) . '.' . $extension;
                $request->file('foto')->storeAs('public', $file_name);
            } else {
                $file_name = "default.png";
            }
            $registro->foto = $file_name;
            $registro->save();
            return redirect()->route('InformacionUsuario');
        } else {
            return view('auth/login');
        }
    }

    public function balanceAnual()
    {
        $Anios = Anio::all();
        return view('administrador/administradorBalanceAnual', compact('Anios'));
    }

    public function excel(Request $request)
    {
        $balance = $request->file('balance');
        $codanio = $request->anio;
        $id = Auth::id();
        Excel::import(new BalanceExport($codanio, $id), $balance);
        return back();
    }

    public function getconosinbalance(Request $request, $codanio)
    {
        $id = Auth::id();
        if ($request->ajax()) {
            $BalanceAnual = Balance::BalanceAnual($codanio, $id);
            return response()->json($BalanceAnual);
        }
    }

    public function eliminarbalance(Request $request)
    {
        $id = Auth::id();
        $codanio = $request->anio;
        Balance::eliminarbalanceanual($codanio, $id);
        return response()->json(['success' => 'El balance se eliminó']);
    }

    public function actualizarbalance(Request $request)
    {
        $id = Auth::id();
        $filas = json_decode(json_encode($_POST['valores']), True);
        foreach ($filas as $fila) {
            $valorbalance =  $fila['valorbalance'];
            $codanio = $fila['anio'];
            $codcontable = $fila['codcontable'];
            $balanceanual = Balance::balance($codanio, $id, $codcontable);
            $balanceanual->valorbalance = $valorbalance;
            $balanceanual->save();
        }

        return response()->json(['success' => 'El balance se actualizó']);
    }

    public function ResumenBalanceAnual()
    {
        if (Auth::check()) {
            $id = Auth::id();
            $Anios = Anio::all();
            $Resumen = [];
            foreach ($Anios as $codanio) {
                $anio = $codanio['codanio'];
                $nomanio = $codanio['nomanio'];
                $Carteraporvencer = Balance::carteraporvencer($anio, $id);
                if ($Carteraporvencer > 0) {
                    $Carteraporvencer = Balance::carteraporvencer($anio, $id);
                    $Carteraquenodevengaintereses = Balance::carteraquenodevengaintereses($anio, $id);
                    $Carteravencida = Balance::carteravencida($anio, $id);
                    $Carteraimproductiva = $Carteraquenodevengaintereses + $Carteravencida;
                    $Carterabruta = $Carteraporvencer + $Carteraimproductiva;
                    $Proviciones =  Balance::proviciones($anio, $id);
                    $Carteraneta = $Carterabruta + $Proviciones;

                    $Cuentasporcobrar = Balance::cuentasporcobrar($anio, $id);
                    $Bienesendaciondepago = Balance::bienesendaciondepago($anio, $id);
                    $Activosfijos = Balance::activosfijos($anio, $id);
                    $Otrosactivos = Balance::otrosactivos($anio, $id);
                    $Totalactivosimproductivos = $Cuentasporcobrar + $Bienesendaciondepago + $Activosfijos + $Otrosactivos + $Carteraimproductiva;

                    $Depositosdesocios = Balance::depositosdesocios($anio, $id);
                    $Obligacionesfinancieras = Balance::obligacionesfinancieras($anio, $id);
                    $Pasivosconcosto = $Depositosdesocios + $Obligacionesfinancieras;

                    $Reservastotales = Balance::reservastotales($anio, $id);

                    $Depositosalavista = Balance::depositosalavista($anio, $id);
                    $de1a30dias = Balance::de1a30dias($anio, $id);
                    $de31a90dias = Balance::de31a90dias($anio, $id);
                    $oblde1a30dias = Balance::oblde1a30dias($anio, $id);
                    $oblde31a90dias = Balance::oblde31a90dias($anio, $id);

                    $Totaldepositoscortoplazo = $Depositosalavista + $de1a30dias + $de31a90dias + $oblde1a30dias + $oblde31a90dias;

                    $Activoproductivo = $Carteraporvencer + Balance::inversiones($anio, $id);
                    $Pasivossincosto =  Balance::totalpasivo($anio, $id) - $Pasivosconcosto;

                    $Resumen = array(
                        "Carteraporvencer" => round($Carteraporvencer, 2),
                        "Carteraquenodevengaintereses" => round($Carteraquenodevengaintereses, 2),
                        "Carteravencida" => round($Carteravencida, 2),
                        "Carterabruta" => round($Carterabruta, 2),
                        "Proviciones" => round($Proviciones, 2),
                        "Carteraneta" => round($Carteraneta, 2),
                        "Cuentasporcobrar" => round($Cuentasporcobrar, 2),
                        "Bienesendaciondepago" => round($Bienesendaciondepago, 2),
                        "Activosfijos" => round($Activosfijos, 2),
                        "Otrosactivos" => round($Otrosactivos, 2),
                        "Totalactivosimproductivos" => round($Totalactivosimproductivos, 2),
                        "Depositosdesocios" => round($Depositosdesocios, 2),
                        "Obligacionesfinancieras" => round($Obligacionesfinancieras, 2),
                        "Pasivosconcosto" => round($Pasivosconcosto, 2),
                        "Reservastotales" => round($Reservastotales, 2),
                        "Depositosalavista" => round($Depositosalavista, 2),
                        "de1a30dias" => round($de1a30dias, 2),
                        "de31a90dias" => round($de31a90dias, 2),
                        "oblde1a30dias" => round($oblde1a30dias, 2),
                        "oblde31a90dias" => round($oblde31a90dias, 2),
                        "Totaldepositoscortoplazo" => round($Totaldepositoscortoplazo, 2),
                        "Activoproductivo" => round($Activoproductivo, 2),
                        "Pasivossincosto" => round($Pasivossincosto, 2),
                        "nomanio" => $nomanio
                    );
                    $Resultado[] = $Resumen;
                } else {
                }
            }
            return view('administrador/administradorResumenBalanceAnual', compact('Resultado'));
        } else {
            return view('auth/login');
        }
    }

    public function EstadoResultadosAnual()
    {
        $Anios = Anio::all();
        return view('administrador/administradorEstadoResultadosAnual', compact('Anios'));
    }

    public function excelEstadoResultados(Request $request)
    {
        $balance = $request->file('balance');
        $codanio = $request->anio;
        Excel::import(new EstadoResultadosImport($codanio), $balance);
        return back();
    }

    public function IndicadoresAnual()
    {
        if (Auth::check()) {
            $id = Auth::id();
            $Anios = Balance::listaranios($id);
            $Resumen = [];
            foreach ($Anios as $codanio) {
                $anio = $codanio['codanio'];
                $nomanio = $codanio['nomanio'];
                $Datos = IndicadoresAnual::ExisteIndicadoresAnual($anio, $id);
                if (empty($Datos)) {
                    $Carteraporvencer = Balance::carteraporvencer($anio, $id);
                    $Carteraquenodevengaintereses = Balance::carteraquenodevengaintereses($anio, $id);
                    $Carteravencida = Balance::carteravencida($anio, $id);
                    $Carteraimproductiva = $Carteraquenodevengaintereses + $Carteravencida;
                    $Carterabruta = $Carteraporvencer + $Carteraimproductiva;
                    $Proviciones =  Balance::proviciones($anio, $id);
                    $Carteraneta = $Carterabruta + $Proviciones;

                    $Cuentasporcobrar = Balance::cuentasporcobrar($anio, $id);
                    $Bienesendaciondepago = Balance::bienesendaciondepago($anio, $id);
                    $Activosfijos = Balance::activosfijos($anio, $id);
                    $Otrosactivos = Balance::otrosactivos($anio, $id);
                    $Totalactivosimproductivos = $Cuentasporcobrar + $Bienesendaciondepago + $Activosfijos + $Otrosactivos + $Carteraimproductiva;

                    $Depositosdesocios = Balance::depositosdesocios($anio, $id);
                    $Obligacionesfinancieras = Balance::obligacionesfinancieras($anio, $id);
                    $Pasivosconcosto = $Depositosdesocios + $Obligacionesfinancieras;

                    $Reservastotales = Balance::reservastotales($anio, $id);

                    $Depositosalavista = Balance::depositosalavista($anio, $id);
                    $de1a30dias = Balance::de1a30dias($anio, $id);
                    $de31a90dias = Balance::de31a90dias($anio, $id);
                    $oblde1a30dias = Balance::oblde1a30dias($anio, $id);
                    $oblde31a90dias = Balance::oblde31a90dias($anio, $id);

                    $Totaldepositoscortoplazo = $Depositosalavista + $de1a30dias + $de31a90dias + $oblde1a30dias + $oblde31a90dias;

                    $Activoproductivo = $Carteraporvencer + Balance::inversiones($anio, $id);
                    $Pasivossincosto =  Balance::totalpasivo($anio, $id) - $Pasivosconcosto;

                    $Totalactivo = Balance::totalactivo($anio, $id);
                    $Totalpasivo = Balance::totalpasivo($anio, $id);
                    $Totalpatrimonio = Balance::totalpatrimonio($anio, $id);

                    $P1 = - ($Proviciones / $Carteravencida) * 100;
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "P1";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "PROTECCIÓN CARTERA VENCIDA";
                    $Indicador->forindicador = "Provision cartera / Cartera vencida";
                    $Indicador->calindicador = "$Proviciones / $Carteravencida";
                    $Indicador->valorindicador = round($P1, 2);
                    $Indicador->metindicador = "100%";
                    $Indicador->save();
                    $P2 = - ($Proviciones / $Carteraimproductiva) * 100;
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "P2";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "PROTECCIÓN CARTERA IMPRODUCTIVA";
                    $Indicador->forindicador = "Provision cartera / Cartera improductiva";
                    $Indicador->calindicador = "$Proviciones / $Carteraimproductiva";
                    $Indicador->valorindicador = round($P2, 2);
                    $Indicador->metindicador = "100%";
                    $Indicador->save();
                    $P3 = ((($Totalactivo + Balance::proviciones($anio, $id)) - ($Totalactivosimproductivos +  $Totalpasivo -  $Depositosdesocios)) / ($Totalpatrimonio + $Depositosdesocios)) * 100;
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "P3";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "SOLVENCIA";
                    $Indicador->forindicador = "((activo total + provisiones) - (activos improductivos netos + pasivo total - Depositos de socios)) / (Patrimonio + Depositos de socios)";
                    $Indicador->calindicador = "(($Totalactivo + $Proviciones) - ($Totalactivosimproductivos +  $Totalpasivo -  $Depositosdesocios)) / ($Totalpatrimonio + $Depositosdesocios)";
                    $Indicador->valorindicador = round($P3, 2);
                    $Indicador->metindicador = ">=111%";
                    $Indicador->save();
                    $P4 = ($Totalpatrimonio / $Totalactivo) * 100;
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "P4";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "CAPACIDAD PATRIMONIAL";
                    $Indicador->forindicador = "Patrimonio / Total Activo";
                    $Indicador->calindicador = "$Totalpatrimonio / $Totalactivo";
                    $Indicador->valorindicador = round($P4, 2);
                    $Indicador->metindicador = "15%";
                    $Indicador->save();

                    $E1 = ($Carteraneta / $Totalactivo) * 100;
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "E1";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "PARTICIPACIÓN DE CARTERA NETA";
                    $Indicador->forindicador = "Cartera neta / Total Activo";
                    $Indicador->calindicador = "$Carteraneta / $Totalactivo";
                    $Indicador->valorindicador = round($E1, 2);
                    $Indicador->metindicador = "70% - 80%";
                    $Indicador->save();
                    $E2 = (Balance::inversiones($anio, $id) / $Totalactivo) * 100;
                    $val = Balance::inversiones($anio, $id);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "E2";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "PARTICIPACIÓN DE INVERSIONES NETAS";
                    $Indicador->forindicador = "Inversiones netas / Total Activo";
                    $Indicador->calindicador = "$val / $Totalactivo";
                    $Indicador->valorindicador = round($E2, 2);
                    $Indicador->metindicador = "<=16%";
                    $Indicador->save();
                    $E3 = ($Depositosdesocios / $Totalactivo) * 100;
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "E3";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "PARTICIPACIÓN DE AHORROS";
                    $Indicador->forindicador = "Total Depositos de socios / Total Activo";
                    $Indicador->calindicador = "$Depositosdesocios / $Totalactivo";
                    $Indicador->valorindicador = round($E3, 2);
                    $Indicador->metindicador = "70% - 80%";
                    $Indicador->save();
                    $E4 = ($Obligacionesfinancieras / $Totalactivo) * 100;
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "E4";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "PARTICIPACIÓN DE OBLIGACIONES FINANCIERAS";
                    $Indicador->forindicador = "Obligaciones Financieras / Total Activo";
                    $Indicador->calindicador = "$Obligacionesfinancieras / $Totalactivo";
                    $Indicador->valorindicador = round($E4, 2);
                    $Indicador->metindicador = "<=2%";
                    $Indicador->save();
                    $E5 = (Balance::capitalsocial($anio, $id) / $Totalactivo) * 100;
                    $val = Balance::capitalsocial($anio, $id);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "E5";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "PARTICIPACIÓN DE APORTACIONES DE SOCIOS";
                    $Indicador->forindicador = "Total Certificados de Aportacion / Total Activo";
                    $Indicador->calindicador = "$val / $Totalactivo";
                    $Indicador->valorindicador = round($E5, 2);
                    $Indicador->metindicador = "<=20%";
                    $Indicador->save();
                    $E6 = ($Reservastotales / $Totalactivo) * 100;
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "E6";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "PARTICIPACIÓN DE RESERVAS";
                    $Indicador->forindicador = "Total Reservas / Total Activo";
                    $Indicador->calindicador = "$Reservastotales / $Totalactivo";
                    $Indicador->valorindicador = round($E6, 2);
                    $Indicador->metindicador = ">=10%";
                    $Indicador->save();

                    $R1 = ((Balance::interesesydescuentosdeinversionesentitulosvalores($anio, $id) + Balance::interesesydescuentosdecarteradecreditos($anio, $id)) / ((Balance::carteraporvencer($anio, $id) + Balance::carteraporvencer($anio - 1, $id)) / 2)) * 100;
                    $val = (Balance::interesesydescuentosdeinversionesentitulosvalores($anio, $id) + Balance::interesesydescuentosdecarteradecreditos($anio, $id));
                    $val2 = (Balance::carteraporvencer($anio, $id) + Balance::carteraporvencer($anio - 1, $id)) / 2;
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "R1";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "RENDIMIENTO NETO DE PRÉSTAMOS";
                    $Indicador->forindicador = "Ingresos por cartera / Cartera Por Vencer Promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R1, 2);
                    $Indicador->metindicador = ">10%";
                    $Indicador->save();
                    $R2 = (Balance::interesesydescuentosdeinversionesentitulosvalores($anio, $id) / ((Balance::inversiones($anio, $id) + Balance::inversiones($anio - 1, $id)) / 2)) * 100;
                    $val = Balance::interesesydescuentosdeinversionesentitulosvalores($anio, $id);
                    $val2 = ((Balance::inversiones($anio, $id) + Balance::inversiones($anio - 1, $id)) / 2);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "R2";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "RENDIMIENTO NETO DE INVERSIONES";
                    $Indicador->forindicador = "Ingresos por inversiones / Inversiones Promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R2, 2);
                    $Indicador->metindicador = "Tasa del rendimiento";
                    $Indicador->save();
                    $R3 = (Balance::interesesydescuentosganados($anio, $id) / (($Carteraporvencer + Balance::inversiones($anio - 1, $id) + $Carteraporvencer + Balance::inversiones($anio, $id)) / 2)) * 100;
                    $val = Balance::interesesydescuentosganados($anio, $id);
                    $val2 = (($Carteraporvencer + Balance::inversiones($anio - 1, $id) + $Carteraporvencer + Balance::inversiones($anio, $id)) / 2);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "R3";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "RENDIMIENTO DE ACTIVOS PRODUCTIVOS";
                    $Indicador->forindicador = "ITotal Intereses ganados / Activo produtivo promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R3, 2);
                    $Indicador->metindicador = ">18%";
                    $Indicador->save();
                    $R4 = (Balance::obligacionesconelpublico($anio, $id) / ((Balance::obligacionesconelpublico($anio - 1, $id) + Balance::obligacionesconelpublico($anio, $id)) / 2)) * 100;
                    $val = Balance::obligacionesconelpublico($anio, $id);
                    $val2 = ((Balance::obligacionesconelpublico($anio - 1, $id) + Balance::obligacionesconelpublico($anio, $id)) / 2);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "R4";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "COSTO FINANCIERO AHORROS DE SOCIOS";
                    $Indicador->forindicador = "Intereses pagados por depositos / Depositos de Socios Promedio ";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R4, 2);
                    $Indicador->metindicador = "5%";
                    $Indicador->save();
                    $R5 = (Balance::depositosdeahorro($anio, $id) / ((Balance::depositosalavista($anio - 1, $id) + Balance::depositosalavista($anio, $id)) / 2)) * 100;
                    $val = Balance::depositosdeahorro($anio, $id);
                    $val2 = ((Balance::depositosalavista($anio - 1, $id) + Balance::depositosalavista($anio, $id)) / 2);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "R5";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "COSTO FINANCIERO AHORROS A LA VISTA";
                    $Indicador->forindicador = "Intereses pagados por ahorros a la vista / Ahorros a la vista Promedio ";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R5, 2);
                    $Indicador->metindicador = "<=2%";
                    $Indicador->save();
                    $R6 = (Balance::depositosaplazo($anio, $id) / ((Balance::depositosaplazo2($anio - 1, $id) + Balance::depositosaplazo2($anio, $id)) / 2)) * 100;
                    $val = Balance::depositosaplazo($anio, $id);
                    $val2 = ((Balance::depositosaplazo2($anio - 1, $id) + Balance::depositosaplazo2($anio, $id)) / 2);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "R6";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "COSTO FINANCIERO DEPÓSITOS A PLAZO";
                    $Indicador->forindicador = "Intereses pagados por depositos a plazo / Depositos a plazo Promedio  ";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R6, 2);
                    $Indicador->metindicador = ">=5%";
                    $Indicador->save();
                    $R7 = (Balance::obligacionesfinancieras4103($anio, $id) / ((Balance::obligacionesfinancieras($anio - 1, $id) + Balance::obligacionesfinancieras($anio, $id)) / 2)) * 100;
                    $val = Balance::obligacionesfinancieras4103($anio, $id);
                    $val2 = ((Balance::obligacionesfinancieras($anio - 1, $id) + Balance::obligacionesfinancieras($anio, $id)) / 2);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "R7";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "COSTO FINANCIERO OBLIGACIONES FINANCIERAS";
                    $Indicador->forindicador = "Intereses pagados por obligaciones financieras / Obligaciones financieras Promedio ";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R7, 2);
                    $Indicador->metindicador = ">=10%";
                    $Indicador->save();
                    $R8 = (Balance::interesescausados($anio, $id) / (((Balance::totalpasivo($anio - 1, $id) - $Pasivosconcosto) + (Balance::totalpasivo($anio, $id) - $Pasivosconcosto)) / 2)) * 100;
                    $val = Balance::interesescausados($anio, $id);
                    $val2 = (((Balance::totalpasivo($anio - 1, $id) - $Pasivosconcosto) + (Balance::totalpasivo($anio, $id) - $Pasivosconcosto)) / 2);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "R8";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "COSTO FINANCIERO TOTAL";
                    $Indicador->forindicador = "Total intereses pagados / Pasivos con costo promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R8, 2);
                    $Indicador->metindicador = "<=5%";
                    $Indicador->save();
                    $R9 = (Balance::interesesydescuentosganados($anio, $id) / Balance::interesescausados($anio, $id)) * 100;
                    $val = Balance::interesesydescuentosganados($anio, $id);
                    $val2 = Balance::interesescausados($anio, $id);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "R9";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "MARGEN BRUTO ";
                    $Indicador->forindicador = "Total intereses ganados / Total intereses pagados";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R9, 2);
                    $Indicador->metindicador = "300%";
                    $Indicador->save();
                    $R10 = (Balance::gastosoperacion($anio, $id) / ((Balance::interesesydescuentosganados($anio, $id) - Balance::interesescausados($anio, $id)) + Balance::ingresosporservicios($anio, $id) - $Proviciones)) * 100;
                    $val = Balance::gastosoperacion($anio, $id);
                    $val2 = ((Balance::interesesydescuentosganados($anio, $id) - Balance::interesescausados($anio, $id)) + Balance::ingresosporservicios($anio, $id) - $Proviciones);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "R10";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "GRADO DE ABSORCIÓN";
                    $Indicador->forindicador = "Gastos de operación / (Margen bruto financiero - Provisiones)";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R10, 2);
                    $Indicador->metindicador = "<100%";
                    $Indicador->save();
                    $R11 = (Balance::gastosoperacion($anio, $id) / ((Balance::totalactivo($anio - 1, $id) + Balance::totalactivo($anio, $id)) / 2)) * 100;
                    $val = Balance::gastosoperacion($anio, $id);
                    $val2 = ((Balance::totalactivo($anio - 1, $id) + Balance::totalactivo($anio, $id)) / 2);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "R11";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "TASA EFICIENCIA DEL ACTIVO";
                    $Indicador->forindicador = "Gastos de operación / Activo total promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R11, 2);
                    $Indicador->metindicador = "<5%";
                    $Indicador->save();
                    $R12 = (Balance::gastosdepersonal($anio, $id) / ((Balance::totalactivo($anio - 1, $id) + Balance::totalactivo($anio, $id)) / 2)) * 100;
                    $val = Balance::gastosdepersonal($anio, $id);
                    $val2 = ((Balance::totalactivo($anio - 1, $id) + Balance::totalactivo($anio, $id)) / 2);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "R12";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "TASA EFICIENCIA GASTOS DE PERSONAL";
                    $Indicador->forindicador = "Gastos de personal / Activo total promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R12, 2);
                    $Indicador->metindicador = "<3%";
                    $Indicador->save();
                    $R13 = ($Proviciones / (((Balance::carteraporvencer($anio - 1, $id) + Balance::carteraquenodevengaintereses($anio - 1, $id) + Balance::carteravencida($anio - 1, $id) + Balance::carteraquenodevengaintereses($anio - 1, $id) + Balance::carteravencida($anio - 1, $id)) + (Balance::carteraporvencer($anio, $id) + Balance::carteraquenodevengaintereses($anio, $id) + Balance::carteravencida($anio, $id) + Balance::carteraquenodevengaintereses($anio, $id) + Balance::carteravencida($anio, $id))) / 2)) * 100;
                    $val = $Proviciones;
                    $val2 = (((Balance::carteraporvencer($anio - 1, $id) + Balance::carteraquenodevengaintereses($anio - 1, $id) + Balance::carteravencida($anio - 1, $id) + Balance::carteraquenodevengaintereses($anio - 1, $id) + Balance::carteravencida($anio - 1, $id)) + (Balance::carteraporvencer($anio, $id) + Balance::carteraquenodevengaintereses($anio, $id) + Balance::carteravencida($anio, $id) + Balance::carteraquenodevengaintereses($anio, $id) + Balance::carteravencida($anio, $id))) / 2);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "R13";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "TASA DE PROVISIONES";
                    $Indicador->forindicador = "Gasto de provisiones / Cartera bruta promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R13, 2);
                    $Indicador->metindicador = "<2%";
                    $Indicador->save();
                    $R14 = (Balance::perdidasyganancias($anio, $id) / ((Balance::totalactivo($anio - 1, $id) + Balance::totalactivo($anio, $id)) / 2)) * 100;
                    $val = Balance::perdidasyganancias($anio, $id);
                    $val2 = ((Balance::totalactivo($anio - 1, $id) + Balance::totalactivo($anio, $id)) / 2);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "R14";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "RENDIMIENTO DEL ACTIVO TOTAL";
                    $Indicador->forindicador = "Resultado del ejercicio / Activo  promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R14, 2);
                    $Indicador->metindicador = "No inflación";
                    $Indicador->save();
                    $R15 = (Balance::perdidasyganancias($anio, $id) / ((Balance::totalpatrimonio($anio - 1, $id) + Balance::totalpatrimonio($anio, $id)) / 2)) * 100;
                    $val = Balance::perdidasyganancias($anio, $id);
                    $val2 = ((Balance::totalpatrimonio($anio - 1, $id) + Balance::totalpatrimonio($anio, $id)) / 2);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "R15";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "RENDIMIENTO DEL PATRIMONIO";
                    $Indicador->forindicador = "Resultado del ejercicio / Patrimonio promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R15, 2);
                    $Indicador->metindicador = ">10%";
                    $Indicador->save();
                    $R16 = (Balance::perdidasyganancias($anio, $id) / ((Balance::capitalsocial($anio - 1, $id) + Balance::capitalsocial($anio, $id)) / 2)) * 100;
                    $val = Balance::perdidasyganancias($anio, $id);
                    $val2 = ((Balance::capitalsocial($anio - 1, $id) + Balance::capitalsocial($anio, $id)) / 2);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "R16";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "RENDIMIENTO DE CERTIFICADOS DE APORTACIÓN";
                    $Indicador->forindicador = "Resultado del ejercicio / Certificados de aportacion  promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R16, 2);
                    $Indicador->metindicador = ">12%";
                    $Indicador->save();

                    $L1 = (Balance::fondosdisponibles($anio, $id) / $Totaldepositoscortoplazo) * 100;
                    $val = Balance::fondosdisponibles($anio, $id);
                    $val2 = $Totaldepositoscortoplazo;
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "L1";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "LIQUIDEZ CORRIENTE";
                    $Indicador->forindicador = "Fondos disponibles / Depositos de socios a corto plazo";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($L1, 2);
                    $Indicador->metindicador = "15% - 20%";
                    $Indicador->save();
                    $L2 = ((Balance::fondosdisponibles($anio, $id) + Balance::inversiones($anio, $id)) / Balance::depositosdesocios($anio, $id)) * 100;
                    $val = Balance::fondosdisponibles($anio, $id);
                    $val11 = Balance::inversiones($anio, $id);
                    $val2 = Balance::depositosdesocios($anio, $id);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "L2";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "LIQUIDEZ GENERAL";
                    $Indicador->forindicador = "Fondos disponibles + Inversiones / Depositos de socios";
                    $Indicador->calindicador = "($val + $val11) / $val2";
                    $Indicador->valorindicador = round($L2, 2);
                    $Indicador->metindicador = "20%";
                    $Indicador->save();

                    $A1 = ($Carteravencida / $Carterabruta) * 100;
                    $val = $Carteravencida;
                    $val2 = $Carterabruta;
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "A1";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "INDICADOR DE CARTERA VENCIDA";
                    $Indicador->forindicador = "Total Cartera Vencida / Total Cartera Bruta";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($A1, 2);
                    $Indicador->metindicador = "<=3%";
                    $Indicador->save();
                    $A2 = ($Carteraimproductiva / $Carterabruta) * 100;
                    $val = $Carteraimproductiva;
                    $val2 = $Carterabruta;
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "A2";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "INDICADOR DE MOROSIDAD AMPLIADA";
                    $Indicador->forindicador = "Total Cartera Improductiva / Total Cartera Bruta";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($A2, 2);
                    $Indicador->metindicador = "<=5%";
                    $Indicador->save();
                    $A3 = ($Totalactivosimproductivos / $Totalactivo) * 100;
                    $val = $Totalactivosimproductivos;
                    $val2 = $Totalactivo;
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "A3";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "INDICADOR DE ACTIVOS IMPRODUCTIVOS";
                    $Indicador->forindicador = "Activos Improductivos / Total Activo";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($A3, 2);
                    $Indicador->metindicador = "<=5%";
                    $Indicador->save();
                    $A4 = (($Totalpatrimonio + $Pasivossincosto) / $Totalactivosimproductivos) * 100;
                    $val = $Totalpatrimonio;
                    $val1 = $Pasivossincosto;
                    $val2 = $Totalactivosimproductivos;
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "A4";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "PROTECCIÓN DE ACTIVOS IMPRODUCTIVOS";
                    $Indicador->forindicador = "(Patrimonio + Pasivos sin costo) / Activos Improductivos";
                    $Indicador->calindicador = "($val + $val1) / $val2";
                    $Indicador->valorindicador = round($A4, 2);
                    $Indicador->metindicador = ">=200%";
                    $Indicador->save();

                    $S1 = (Balance::fondosdisponibles($anio, $id) / Balance::fondosdisponibles($anio - 1, $id) - 1) * 100;
                    $val = Balance::fondosdisponibles($anio, $id);
                    $val2 = Balance::fondosdisponibles($anio - 1, $id);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "S1";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE FONDOS DISPONIBLES";
                    $Indicador->forindicador = "(Saldo actual de fondos disponibles / Saldo fondos disponibles periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S1, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S2 = ($Carterabruta / (Balance::carteraporvencer($anio, $id) + Balance::carteraquenodevengaintereses($anio, $id) + Balance::carteravencida($anio, $id) + Balance::proviciones($anio - 1, $id)) - 1) * 100;
                    $val = $Carterabruta;
                    $val2 = (Balance::carteraporvencer($anio, $id) + Balance::carteraquenodevengaintereses($anio, $id) + Balance::carteravencida($anio, $id) + Balance::proviciones($anio - 1, $id));
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "S2";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE PRESTAMOS";
                    $Indicador->forindicador = "(Saldo actual de cartera / Saldo cartera periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S2, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S3 = (Balance::obligacionesconelpublico($anio, $id) / Balance::obligacionesconelpublico($anio - 1, $id) - 1) * 100;
                    $val = Balance::obligacionesconelpublico($anio, $id);
                    $val2 = Balance::obligacionesconelpublico($anio - 1, $id);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "S3";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE DEPOSITOS DE SOCIOS";
                    $Indicador->forindicador = "(Saldo actual de depositos de socios / Saldo depositos de socios periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S3, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S4 = (Balance::depositosalavista($anio, $id) / Balance::depositosalavista($anio - 1, $id) - 1) * 100;
                    $val = Balance::depositosalavista($anio, $id);
                    $val2 = Balance::depositosalavista($anio - 1, $id);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "S4";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE AHORROS A LA VISTA";
                    $Indicador->forindicador = "(Saldo actual de ahorros a la vista / Saldo ahorros a la vista periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S4, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S5 = (Balance::depositosaplazo2($anio, $id) / Balance::depositosaplazo2($anio - 1, $id) - 1) * 100;
                    $val = Balance::depositosaplazo2($anio, $id);
                    $val2 = Balance::depositosaplazo2($anio - 1, $id);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "S5";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE DEPOSITOS A PLAZO";
                    $Indicador->forindicador = "(Saldo actual de depositos a plazo / Saldo depositos a plazo periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S5, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S6 = (Balance::obligacionesfinancieras($anio, $id) / Balance::obligacionesfinancieras($anio - 1, $id) - 1) * 100;
                    $val = Balance::obligacionesfinancieras($anio, $id);
                    $val2 = Balance::obligacionesfinancieras($anio - 1, $id);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "S6";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE OBLIGACIONES FINANCIERAS";
                    $Indicador->forindicador = "(Saldo actual de obligaciones financieras / Saldo obligaciones financieras periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S6, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S7 = (Balance::capitalsocial($anio, $id) / Balance::capitalsocial($anio - 1, $id) - 1) * 100;
                    $val = Balance::capitalsocial($anio, $id);
                    $val2 = Balance::capitalsocial($anio - 1, $id);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "S7";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE CERTIFICADOS DE APORTACION";
                    $Indicador->forindicador = "(Saldo actual de certificados de aportacion / Saldo certificados de aportacion periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S7, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S8 = (Balance::totalpatrimonio($anio, $id) / Balance::totalpatrimonio($anio - 1, $id) - 1) * 100;
                    $val = Balance::totalpatrimonio($anio, $id);
                    $val2 = Balance::totalpatrimonio($anio - 1, $id);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "S8";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DEL PATRIMONIO";
                    $Indicador->forindicador = "(Saldo actual de patrimonio / Saldo patrimonio periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S8, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S9 = (Balance::totalactivo($anio, $id) / Balance::totalactivo($anio - 1, $id) - 1) * 100;
                    $val = Balance::totalactivo($anio, $id);
                    $val2 = Balance::totalactivo($anio - 1, $id);
                    $Indicador = new IndicadoresAnual;
                    $Indicador->codindicador = "S9";
                    $Indicador->id = $id;
                    $Indicador->codanio = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DEL NÚMERO DE SOCIOS";
                    $Indicador->forindicador = "(Total número de socios actual / Saldo número de socios periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S9, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S10 = 0;
                } else {

                    $Carteraporvencer = Balance::carteraporvencer($anio, $id);
                    $Carteraquenodevengaintereses = Balance::carteraquenodevengaintereses($anio, $id);
                    $Carteravencida = Balance::carteravencida($anio, $id);
                    $Carteraimproductiva = $Carteraquenodevengaintereses + $Carteravencida;
                    $Carterabruta = $Carteraporvencer + $Carteraimproductiva;
                    $Proviciones =  Balance::proviciones($anio, $id);
                    $Carteraneta = $Carterabruta + $Proviciones;

                    $Cuentasporcobrar = Balance::cuentasporcobrar($anio, $id);
                    $Bienesendaciondepago = Balance::bienesendaciondepago($anio, $id);
                    $Activosfijos = Balance::activosfijos($anio, $id);
                    $Otrosactivos = Balance::otrosactivos($anio, $id);
                    $Totalactivosimproductivos = $Cuentasporcobrar + $Bienesendaciondepago + $Activosfijos + $Otrosactivos + $Carteraimproductiva;

                    $Depositosdesocios = Balance::depositosdesocios($anio, $id);
                    $Obligacionesfinancieras = Balance::obligacionesfinancieras($anio, $id);
                    $Pasivosconcosto = $Depositosdesocios + $Obligacionesfinancieras;

                    $Reservastotales = Balance::reservastotales($anio, $id);

                    $Depositosalavista = Balance::depositosalavista($anio, $id);
                    $de1a30dias = Balance::de1a30dias($anio, $id);
                    $de31a90dias = Balance::de31a90dias($anio, $id);
                    $oblde1a30dias = Balance::oblde1a30dias($anio, $id);
                    $oblde31a90dias = Balance::oblde31a90dias($anio, $id);

                    $Totaldepositoscortoplazo = $Depositosalavista + $de1a30dias + $de31a90dias + $oblde1a30dias + $oblde31a90dias;

                    $Activoproductivo = $Carteraporvencer + Balance::inversiones($anio, $id);
                    $Pasivossincosto =  Balance::totalpasivo($anio, $id) - $Pasivosconcosto;

                    $Totalactivo = Balance::totalactivo($anio, $id);
                    $Totalpasivo = Balance::totalpasivo($anio, $id);
                    $Totalpatrimonio = Balance::totalpatrimonio($anio, $id);

                    $P1 = - ($Proviciones / $Carteravencida) * 100;
                    $Indicador = IndicadoresAnual::Indicador("P1", $anio, $id);
                    $Indicador->valorindicador = round($P1, 2);
                    $Indicador->save();
                    $P2 = - ($Proviciones / $Carteraimproductiva) * 100;
                    $Indicador = IndicadoresAnual::Indicador("P2", $anio, $id);
                    $Indicador->valorindicador = round($P2, 2);
                    $Indicador->save();
                    $P3 = ((($Totalactivo + Balance::proviciones($anio, $id)) - ($Totalactivosimproductivos +  $Totalpasivo -  $Depositosdesocios)) / ($Totalpatrimonio + $Depositosdesocios)) * 100;
                    $Indicador = IndicadoresAnual::Indicador("P3", $anio, $id);
                    $Indicador->valorindicador = round($P3, 2);
                    $Indicador->save();
                    $P4 = ($Totalpatrimonio / $Totalactivo) * 100;
                    $Indicador = IndicadoresAnual::Indicador("P4", $anio, $id);
                    $Indicador->valorindicador = round($P4, 2);
                    $Indicador->save();

                    $E1 = ($Carteraneta / $Totalactivo) * 100;
                    $Indicador = IndicadoresAnual::Indicador("E1", $anio, $id);
                    $Indicador->valorindicador = round($E1, 2);
                    $Indicador->save();
                    $E2 = (Balance::inversiones($anio, $id) / $Totalactivo) * 100;
                    $Indicador = IndicadoresAnual::Indicador("E2", $anio, $id);
                    $Indicador->valorindicador = round($E2, 2);
                    $Indicador->save();
                    $E3 = ($Depositosdesocios / $Totalactivo) * 100;
                    $Indicador = IndicadoresAnual::Indicador("E3", $anio, $id);
                    $Indicador->valorindicador = round($E3, 2);
                    $Indicador->save();
                    $E4 = ($Obligacionesfinancieras / $Totalactivo) * 100;
                    $Indicador = IndicadoresAnual::Indicador("E4", $anio, $id);
                    $Indicador->valorindicador = round($E4, 2);
                    $Indicador->save();
                    $E5 = (Balance::capitalsocial($anio, $id) / $Totalactivo) * 100;
                    $Indicador = IndicadoresAnual::Indicador("E5", $anio, $id);
                    $Indicador->valorindicador = round($E5, 2);
                    $Indicador->save();
                    $E6 = ($Reservastotales / $Totalactivo) * 100;
                    $Indicador = IndicadoresAnual::Indicador("E6", $anio, $id);
                    $Indicador->valorindicador = round($E6, 2);
                    $Indicador->save();

                    $R1 = ((Balance::interesesydescuentosdeinversionesentitulosvalores($anio, $id) + Balance::interesesydescuentosdecarteradecreditos($anio, $id)) / ((Balance::carteraporvencer($anio, $id) + Balance::carteraporvencer($anio - 1, $id)) / 2)) * 100;
                    $Indicador = IndicadoresAnual::Indicador("R1", $anio, $id);
                    $Indicador->valorindicador = round($R1, 2);
                    $Indicador->save();
                    $R2 = (Balance::interesesydescuentosdeinversionesentitulosvalores($anio, $id) / ((Balance::inversiones($anio, $id) + Balance::inversiones($anio - 1, $id)) / 2)) * 100;
                    $Indicador = IndicadoresAnual::Indicador("R2", $anio, $id);
                    $Indicador->valorindicador = round($R2, 2);
                    $Indicador->save();
                    $R3 = (Balance::interesesydescuentosganados($anio, $id) / (($Carteraporvencer + Balance::inversiones($anio - 1, $id) + $Carteraporvencer + Balance::inversiones($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresAnual::Indicador("R3", $anio, $id);
                    $Indicador->valorindicador = round($R3, 2);
                    $Indicador->save();
                    $R4 = (Balance::obligacionesconelpublico($anio, $id) / ((Balance::obligacionesconelpublico($anio - 1, $id) + Balance::obligacionesconelpublico($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresAnual::Indicador("R4", $anio, $id);
                    $Indicador->valorindicador = round($R4, 2);
                    $Indicador->save();
                    $R5 = (Balance::depositosdeahorro($anio, $id) / ((Balance::depositosalavista($anio - 1, $id) + Balance::depositosalavista($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresAnual::Indicador("R5", $anio, $id);
                    $Indicador->valorindicador = round($R5, 2);
                    $Indicador->save();
                    $R6 = (Balance::depositosaplazo($anio, $id) / ((Balance::depositosaplazo2($anio - 1, $id) + Balance::depositosaplazo2($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresAnual::Indicador("R6", $anio, $id);
                    $Indicador->valorindicador = round($R6, 2);
                    $Indicador->save();
                    $R7 = (Balance::obligacionesfinancieras4103($anio, $id) / ((Balance::obligacionesfinancieras($anio - 1, $id) + Balance::obligacionesfinancieras($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresAnual::Indicador("R7", $anio, $id);
                    $Indicador->valorindicador = round($R7, 2);
                    $Indicador->save();
                    $R8 = (Balance::interesescausados($anio, $id) / (((Balance::totalpasivo($anio - 1, $id) - $Pasivosconcosto) + (Balance::totalpasivo($anio, $id) - $Pasivosconcosto)) / 2)) * 100;
                    $Indicador = IndicadoresAnual::Indicador("R8", $anio, $id);
                    $Indicador->valorindicador = round($R8, 2);
                    $Indicador->save();
                    $R9 = (Balance::interesesydescuentosganados($anio, $id) / Balance::interesescausados($anio, $id)) * 100;
                    $Indicador = IndicadoresAnual::Indicador("R9", $anio, $id);
                    $Indicador->valorindicador = round($R9, 2);
                    $Indicador->save();
                    $R10 = (Balance::gastosoperacion($anio, $id) / ((Balance::interesesydescuentosganados($anio, $id) - Balance::interesescausados($anio, $id)) + Balance::ingresosporservicios($anio, $id) - $Proviciones)) * 100;
                    $Indicador = IndicadoresAnual::Indicador("R10", $anio, $id);
                    $Indicador->valorindicador = round($R10, 2);
                    $Indicador->save();
                    $R11 = (Balance::gastosoperacion($anio, $id) / ((Balance::totalactivo($anio - 1, $id) + Balance::totalactivo($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresAnual::Indicador("R11", $anio, $id);
                    $Indicador->valorindicador = round($R11, 2);
                    $Indicador->save();
                    $R12 = (Balance::gastosdepersonal($anio, $id) / ((Balance::totalactivo($anio - 1, $id) + Balance::totalactivo($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresAnual::Indicador("R12", $anio, $id);
                    $Indicador->valorindicador = round($R12, 2);
                    $Indicador->save();
                    $R13 = ($Proviciones / (((Balance::carteraporvencer($anio - 1, $id) + Balance::carteraquenodevengaintereses($anio - 1, $id) + Balance::carteravencida($anio - 1, $id) + Balance::carteraquenodevengaintereses($anio - 1, $id) + Balance::carteravencida($anio - 1, $id)) + (Balance::carteraporvencer($anio, $id) + Balance::carteraquenodevengaintereses($anio, $id) + Balance::carteravencida($anio, $id) + Balance::carteraquenodevengaintereses($anio, $id) + Balance::carteravencida($anio, $id))) / 2)) * 100;
                    $Indicador = IndicadoresAnual::Indicador("R13", $anio, $id);
                    $Indicador->valorindicador = round($R13, 2);
                    $Indicador->save();
                    $R14 = (Balance::perdidasyganancias($anio, $id) / ((Balance::totalactivo($anio - 1, $id) + Balance::totalactivo($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresAnual::Indicador("R14", $anio, $id);
                    $Indicador->valorindicador = round($R14, 2);
                    $Indicador->save();
                    $R15 = (Balance::perdidasyganancias($anio, $id) / ((Balance::totalpatrimonio($anio - 1, $id) + Balance::totalpatrimonio($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresAnual::Indicador("R15", $anio, $id);
                    $Indicador->valorindicador = round($R15, 2);
                    $Indicador->save();
                    $R16 = (Balance::perdidasyganancias($anio, $id) / ((Balance::capitalsocial($anio - 1, $id) + Balance::capitalsocial($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresAnual::Indicador("R16", $anio, $id);
                    $Indicador->valorindicador = round($R16, 2);
                    $Indicador->save();

                    $L1 = (Balance::fondosdisponibles($anio, $id) / $Totaldepositoscortoplazo) * 100;
                    $Indicador = IndicadoresAnual::Indicador("L1", $anio, $id);
                    $Indicador->valorindicador = round($L1, 2);
                    $Indicador->save();
                    $L2 = ((Balance::fondosdisponibles($anio, $id) + Balance::inversiones($anio, $id)) / Balance::depositosdesocios($anio, $id)) * 100;
                    $Indicador = IndicadoresAnual::Indicador("L2", $anio, $id);
                    $Indicador->valorindicador = round($L2, 2);
                    $Indicador->save();

                    $A1 = ($Carteravencida / $Carterabruta) * 100;
                    $Indicador = IndicadoresAnual::Indicador("A1", $anio, $id);
                    $Indicador->valorindicador = round($A1, 2);
                    $Indicador->save();
                    $A2 = ($Carteraimproductiva / $Carterabruta) * 100;
                    $Indicador = IndicadoresAnual::Indicador("A2", $anio, $id);
                    $Indicador->valorindicador = round($A2, 2);
                    $Indicador->save();
                    $A3 = ($Totalactivosimproductivos / $Totalactivo) * 100;
                    $Indicador = IndicadoresAnual::Indicador("A3", $anio, $id);
                    $Indicador->valorindicador = round($A3, 2);
                    $Indicador->save();
                    $A4 = (($Totalpatrimonio + $Pasivossincosto) / $Totalactivosimproductivos) * 100;
                    $Indicador = IndicadoresAnual::Indicador("A4", $anio, $id);
                    $Indicador->valorindicador = round($A4, 2);
                    $Indicador->save();

                    $S1 = (Balance::fondosdisponibles($anio, $id) / Balance::fondosdisponibles($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresAnual::Indicador("S1", $anio, $id);
                    $Indicador->valorindicador = round($S1, 2);
                    $Indicador->save();
                    $S2 = ($Carterabruta / (Balance::carteraporvencer($anio, $id) + Balance::carteraquenodevengaintereses($anio, $id) + Balance::carteravencida($anio, $id) + Balance::proviciones($anio - 1, $id)) - 1) * 100;
                    $Indicador = IndicadoresAnual::Indicador("S2", $anio, $id);
                    $Indicador->valorindicador = round($S2, 2);
                    $Indicador->save();
                    $S3 = (Balance::obligacionesconelpublico($anio, $id) / Balance::obligacionesconelpublico($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresAnual::Indicador("S3", $anio, $id);
                    $Indicador->valorindicador = round($S3, 2);
                    $Indicador->save();
                    $S4 = (Balance::depositosalavista($anio, $id) / Balance::depositosalavista($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresAnual::Indicador("S4", $anio, $id);
                    $Indicador->valorindicador = round($S4, 2);
                    $Indicador->save();
                    $S5 = (Balance::depositosaplazo2($anio, $id) / Balance::depositosaplazo2($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresAnual::Indicador("S5", $anio, $id);
                    $Indicador->valorindicador = round($S5, 2);
                    $Indicador->save();
                    $S6 = (Balance::obligacionesfinancieras($anio, $id) / Balance::obligacionesfinancieras($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresAnual::Indicador("S6", $anio, $id);
                    $Indicador->valorindicador = round($S6, 2);
                    $Indicador->save();
                    $S7 = (Balance::capitalsocial($anio, $id) / Balance::capitalsocial($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresAnual::Indicador("S7", $anio, $id);
                    $Indicador->valorindicador = round($S7, 2);
                    $Indicador->save();
                    $S8 = (Balance::totalpatrimonio($anio, $id) / Balance::totalpatrimonio($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresAnual::Indicador("S8", $anio, $id);
                    $Indicador->valorindicador = round($S8, 2);
                    $Indicador->save();
                    $S9 = (Balance::totalactivo($anio, $id) / Balance::totalactivo($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresAnual::Indicador("S9", $anio, $id);
                    $Indicador->valorindicador = round($S9, 2);
                    $Indicador->save();
                    $S10 = 0;
                }
            }

            $Anios = Balance::listaranios($id);
            return view('administrador/administradorIndicadoresAnual', compact('Anios'));
        } else {
            return view('auth/login');
        }
    }

    public function indicadores(Request $request, $codanio)
    {
        $id = Auth::id();
        $Resumen = [];

        if ($request->ajax()) {
            $Indicadores = IndicadoresAnual::Indicadores($codanio, $id);
            foreach ($Indicadores as $indi) {
                $Indicadoresanioanterior = IndicadoresAnual::Indicador($indi['codindicador'], $codanio - 1, $id);
                $Resumen = array(
                    "codindicador" => $indi['codindicador'],
                    "nomindicador" => $indi['nomindicador'],
                    "forindicador" => $indi['forindicador'],
                    "calindicador" => $indi['calindicador'],
                    "metindicador" => $indi['metindicador'],
                    "nomanio" => $indi['nomanio'],
                    "valorindicador" => $indi['valorindicador'],
                    "nomanio2" => $Indicadoresanioanterior->nomanio,
                    "forindicador2" => $Indicadoresanioanterior->forindicador,
                    "calindicador2" => $Indicadoresanioanterior->calindicador,
                    "valorindicador2" => $Indicadoresanioanterior->valorindicador,
                );
                $Resultado[] = $Resumen;
            }
            return response()->json($Resultado);
        }
    }


    public function BalanceMensual()
    {
        if (Auth::check()) {
            $Anios = Anio::all();
            $Meses = Mes::all();
            return view('administrador/administradorBalanceMensual', compact('Anios', 'Meses'));
        } else {
            return view('auth/login');
        }
    }

    public function Meses(Request $request, $codanio)
    {
        if ($request->ajax()) {
            $AnioMes = AnioMes::AnioMes($codanio);
            return response()->json($AnioMes);
        }
    }

    public function conosinbalancemensual(Request $request, $codaniomes)
    {
        $id = Auth::id();
        if ($request->ajax()) {
            $BalanceMensual = BalanceMensual::BalanceMensual($codaniomes, $id);
            return response()->json($BalanceMensual);
        }
    }

    public function excelmensual(Request $request)
    {
        $balance = $request->file('balance');
        $codanio = $request->mes;
        $id = Auth::id();
        Excel::import(new BalanceMensualImport($codanio, $id), $balance);
        return back();
    }

    public function eliminarbalancemensual(Request $request)
    {
        $id = Auth::id();
        $codanio = $request->anio;
        BalanceMensual::eliminarbalancemensual($codanio, $id);
        return response()->json(['success' => 'El balance se eliminó']);
    }

    public function actualizarbalancemensual(Request $request)
    {
        $id = Auth::id();
        $filas = json_decode(json_encode($_POST['valores']), True);
        foreach ($filas as $fila) {
            $valorbalance =  $fila['valorbalance'];
            $codanio = $fila['anio'];
            $codcontable = $fila['codcontable'];
            $balanceanual = BalanceMensual::balance($codanio, $id, $codcontable);
            $balanceanual->valorbalance = $valorbalance;
            $balanceanual->save();
        }

        return response()->json(['success' => 'El balance se actualizó']);
    }

    public function ResumenBalanceMensual()
    {
        if (Auth::check()) {
            $Anios = Anio::all();
            $Meses = Mes::all();
            return view('administrador/administradorResumenBalanceMensual', compact('Anios', 'Meses'));
        } else {
            return view('auth/login');
        }
    }

    public function conosinresumenmensual($codaniomes)
    {

        $id = Auth::id();
        $Resumen = [];
        $Resultado = [];
        $anio = $codaniomes;
        $AnioMes = AnioMes::CodigoAnioMes($anio);
        $nomanio = $AnioMes->first()->nomanio;
        $nommes = $AnioMes->first()->nommes;
        $Carteraporvencer = BalanceMensual::carteraporvencer($anio, $id);
        if ($Carteraporvencer > 0) {
            $Carteraporvencer = BalanceMensual::carteraporvencer($anio, $id);
            $Carteraquenodevengaintereses = BalanceMensual::carteraquenodevengaintereses($anio, $id);
            $Carteravencida = BalanceMensual::carteravencida($anio, $id);
            $Carteraimproductiva = $Carteraquenodevengaintereses + $Carteravencida;
            $Carterabruta = $Carteraporvencer + $Carteraimproductiva;
            $Proviciones =  BalanceMensual::proviciones($anio, $id);
            $Carteraneta = $Carterabruta + $Proviciones;

            $Cuentasporcobrar = BalanceMensual::cuentasporcobrar($anio, $id);
            $Bienesendaciondepago = BalanceMensual::bienesendaciondepago($anio, $id);
            $Activosfijos = BalanceMensual::activosfijos($anio, $id);
            $Otrosactivos = BalanceMensual::otrosactivos($anio, $id);
            $Totalactivosimproductivos = $Cuentasporcobrar + $Bienesendaciondepago + $Activosfijos + $Otrosactivos + $Carteraimproductiva;

            $Depositosdesocios = BalanceMensual::depositosdesocios($anio, $id);
            $Obligacionesfinancieras = BalanceMensual::obligacionesfinancieras($anio, $id);
            $Pasivosconcosto = $Depositosdesocios + $Obligacionesfinancieras;

            $Reservastotales = BalanceMensual::reservastotales($anio, $id);

            $Depositosalavista = BalanceMensual::depositosalavista($anio, $id);
            $de1a30dias = BalanceMensual::de1a30dias($anio, $id);
            $de31a90dias = BalanceMensual::de31a90dias($anio, $id);
            $oblde1a30dias = BalanceMensual::oblde1a30dias($anio, $id);
            $oblde31a90dias = BalanceMensual::oblde31a90dias($anio, $id);

            $Totaldepositoscortoplazo = $Depositosalavista + $de1a30dias + $de31a90dias + $oblde1a30dias + $oblde31a90dias;

            $Activoproductivo = $Carteraporvencer + BalanceMensual::inversiones($anio, $id);
            $Pasivossincosto =  BalanceMensual::totalpasivo($anio, $id) - $Pasivosconcosto;

            $Resumen = array(
                "Carteraporvencer" =>  round($Carteraporvencer, 2),
                "Carteraquenodevengaintereses" =>  round($Carteraquenodevengaintereses, 2),
                "Carteravencida" =>  round($Carteravencida, 2),
                "Carterabruta" =>  round($Carterabruta, 2),
                "Proviciones" =>  round($Proviciones, 2),
                "Carteraneta" =>  round($Carteraneta, 2),
                "Cuentasporcobrar" =>  round($Cuentasporcobrar, 2),
                "Bienesendaciondepago" =>  round($Bienesendaciondepago, 2),
                "Activosfijos" =>  round($Activosfijos, 2),
                "Otrosactivos" =>  round($Otrosactivos, 2),
                "Totalactivosimproductivos" =>  round($Totalactivosimproductivos, 2),
                "Depositosdesocios" =>  round($Depositosdesocios, 2),
                "Obligacionesfinancieras" =>  round($Obligacionesfinancieras, 2),
                "Pasivosconcosto" =>  round($Pasivosconcosto, 2),
                "Reservastotales" =>  round($Reservastotales, 2),
                "Depositosalavista" =>  round($Depositosalavista, 2),
                "de1a30dias" =>  round($de1a30dias, 2),
                "de31a90dias" =>  round($de31a90dias, 2),
                "oblde1a30dias" =>  round($oblde1a30dias, 2),
                "oblde31a90dias" =>  round($oblde31a90dias, 2),
                "Totaldepositoscortoplazo" =>  round($Totaldepositoscortoplazo, 2),
                "Activoproductivo" =>  round($Activoproductivo, 2),
                "Pasivossincosto" =>  round($Pasivossincosto, 2),
                "nomanio" => $nomanio,
                "nommes" => $nommes
            );
            $Resultado[] = $Resumen;
        } else {
        }
        return response()->json($Resultado);
    }

    public function IndicadoresMensual()
    {
        if (Auth::check()) {
            $id = Auth::id();
            $Anios = BalanceMensual::listaranios($id);
            $Resumen = [];
            foreach ($Anios as $codanio) {
                $anio = $codanio['codaniomes'];
                $nomanio = $codanio['nomanio'];
                $nommes = $codanio['nommes'];
                $Datos = IndicadoresMensual::Existeindicadoresmensual($anio, $id);
                if (empty($Datos)) {
                    $Carteraporvencer = BalanceMensual::carteraporvencer($anio, $id);
                    $Carteraquenodevengaintereses = BalanceMensual::carteraquenodevengaintereses($anio, $id);
                    $Carteravencida = BalanceMensual::carteravencida($anio, $id);
                    $Carteraimproductiva = $Carteraquenodevengaintereses + $Carteravencida;
                    $Carterabruta = $Carteraporvencer + $Carteraimproductiva;
                    $Proviciones =  BalanceMensual::proviciones($anio, $id);
                    $Carteraneta = $Carterabruta + $Proviciones;

                    $Cuentasporcobrar = BalanceMensual::cuentasporcobrar($anio, $id);
                    $Bienesendaciondepago = BalanceMensual::bienesendaciondepago($anio, $id);
                    $Activosfijos = BalanceMensual::activosfijos($anio, $id);
                    $Otrosactivos = BalanceMensual::otrosactivos($anio, $id);
                    $Totalactivosimproductivos = $Cuentasporcobrar + $Bienesendaciondepago + $Activosfijos + $Otrosactivos + $Carteraimproductiva;

                    $Depositosdesocios = BalanceMensual::depositosdesocios($anio, $id);
                    $Obligacionesfinancieras = BalanceMensual::obligacionesfinancieras($anio, $id);
                    $Pasivosconcosto = $Depositosdesocios + $Obligacionesfinancieras;

                    $Reservastotales = BalanceMensual::reservastotales($anio, $id);

                    $Depositosalavista = BalanceMensual::depositosalavista($anio, $id);
                    $de1a30dias = BalanceMensual::de1a30dias($anio, $id);
                    $de31a90dias = BalanceMensual::de31a90dias($anio, $id);
                    $oblde1a30dias = BalanceMensual::oblde1a30dias($anio, $id);
                    $oblde31a90dias = BalanceMensual::oblde31a90dias($anio, $id);

                    $Totaldepositoscortoplazo = $Depositosalavista + $de1a30dias + $de31a90dias + $oblde1a30dias + $oblde31a90dias;

                    $Activoproductivo = $Carteraporvencer + BalanceMensual::inversiones($anio, $id);
                    $Pasivossincosto =  BalanceMensual::totalpasivo($anio, $id) - $Pasivosconcosto;

                    $Totalactivo = BalanceMensual::totalactivo($anio, $id);
                    $Totalpasivo = BalanceMensual::totalpasivo($anio, $id);
                    $Totalpatrimonio = BalanceMensual::totalpatrimonio($anio, $id);

                    $P1 = - ($Proviciones / $Carteravencida) * 100;
                    $Indicador = new IndicadoresMensual();
                    $Indicador->codindicador = "P1";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "PROTECCIÓN CARTERA VENCIDA";
                    $Indicador->forindicador = "Provision cartera / Cartera vencida";
                    $Indicador->calindicador = "$Proviciones / $Carteravencida";
                    $Indicador->valorindicador = round($P1, 2);
                    $Indicador->metindicador = "100%";
                    $Indicador->save();
                    $P2 = - ($Proviciones / $Carteraimproductiva) * 100;
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "P2";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "PROTECCIÓN CARTERA IMPRODUCTIVA";
                    $Indicador->forindicador = "Provision cartera / Cartera improductiva";
                    $Indicador->calindicador = "$Proviciones / $Carteraimproductiva";
                    $Indicador->valorindicador = round($P2, 2);
                    $Indicador->metindicador = "100%";
                    $Indicador->save();
                    $P3 = ((($Totalactivo + BalanceMensual::proviciones($anio, $id)) - ($Totalactivosimproductivos +  $Totalpasivo -  $Depositosdesocios)) / ($Totalpatrimonio + $Depositosdesocios)) * 100;
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "P3";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "SOLVENCIA";
                    $Indicador->forindicador = "((activo total + provisiones) - (activos improductivos netos + pasivo total - Depositos de socios)) / (Patrimonio + Depositos de socios)";
                    $Indicador->calindicador = "(($Totalactivo + $Proviciones) - ($Totalactivosimproductivos +  $Totalpasivo -  $Depositosdesocios)) / ($Totalpatrimonio + $Depositosdesocios)";
                    $Indicador->valorindicador = round($P3, 2);
                    $Indicador->metindicador = ">=111%";
                    $Indicador->save();
                    $P4 = ($Totalpatrimonio / $Totalactivo) * 100;
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "P4";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "CAPACIDAD PATRIMONIAL";
                    $Indicador->forindicador = "Patrimonio / Total Activo";
                    $Indicador->calindicador = "$Totalpatrimonio / $Totalactivo";
                    $Indicador->valorindicador = round($P4, 2);
                    $Indicador->metindicador = "15%";
                    $Indicador->save();

                    $E1 = ($Carteraneta / $Totalactivo) * 100;
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "E1";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "PARTICIPACIÓN DE CARTERA NETA";
                    $Indicador->forindicador = "Cartera neta / Total Activo";
                    $Indicador->calindicador = "$Carteraneta / $Totalactivo";
                    $Indicador->valorindicador = round($E1, 2);
                    $Indicador->metindicador = "70% - 80%";
                    $Indicador->save();
                    $E2 = (BalanceMensual::inversiones($anio, $id) / $Totalactivo) * 100;
                    $val = BalanceMensual::inversiones($anio, $id);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "E2";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "PARTICIPACIÓN DE INVERSIONES NETAS";
                    $Indicador->forindicador = "Inversiones netas / Total Activo";
                    $Indicador->calindicador = "$val / $Totalactivo";
                    $Indicador->valorindicador = round($E2, 2);
                    $Indicador->metindicador = "<=16%";
                    $Indicador->save();
                    $E3 = ($Depositosdesocios / $Totalactivo) * 100;
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "E3";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "PARTICIPACIÓN DE AHORROS";
                    $Indicador->forindicador = "Total Depositos de socios / Total Activo";
                    $Indicador->calindicador = "$Depositosdesocios / $Totalactivo";
                    $Indicador->valorindicador = round($E3, 2);
                    $Indicador->metindicador = "70% - 80%";
                    $Indicador->save();
                    $E4 = ($Obligacionesfinancieras / $Totalactivo) * 100;
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "E4";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "PARTICIPACIÓN DE OBLIGACIONES FINANCIERAS";
                    $Indicador->forindicador = "Obligaciones Financieras / Total Activo";
                    $Indicador->calindicador = "$Obligacionesfinancieras / $Totalactivo";
                    $Indicador->valorindicador = round($E4, 2);
                    $Indicador->metindicador = "<=2%";
                    $Indicador->save();
                    $E5 = (BalanceMensual::capitalsocial($anio, $id) / $Totalactivo) * 100;
                    $val = BalanceMensual::capitalsocial($anio, $id);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "E5";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "PARTICIPACIÓN DE APORTACIONES DE SOCIOS";
                    $Indicador->forindicador = "Total Certificados de Aportacion / Total Activo";
                    $Indicador->calindicador = "$val / $Totalactivo";
                    $Indicador->valorindicador = round($E5, 2);
                    $Indicador->metindicador = "<=20%";
                    $Indicador->save();
                    $E6 = ($Reservastotales / $Totalactivo) * 100;
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "E6";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "PARTICIPACIÓN DE RESERVAS";
                    $Indicador->forindicador = "Total Reservas / Total Activo";
                    $Indicador->calindicador = "$Reservastotales / $Totalactivo";
                    $Indicador->valorindicador = round($E6, 2);
                    $Indicador->metindicador = ">=10%";
                    $Indicador->save();

                    $R1 = ((BalanceMensual::interesesydescuentosdeinversionesentitulosvalores($anio, $id) + BalanceMensual::interesesydescuentosdecarteradecreditos($anio, $id)) / ((BalanceMensual::carteraporvencer($anio, $id) + BalanceMensual::carteraporvencer($anio - 1, $id)) / 2)) * 100;
                    $val = (BalanceMensual::interesesydescuentosdeinversionesentitulosvalores($anio, $id) + BalanceMensual::interesesydescuentosdecarteradecreditos($anio, $id));
                    $val2 = (BalanceMensual::carteraporvencer($anio, $id) + BalanceMensual::carteraporvencer($anio - 1, $id)) / 2;
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "R1";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "RENDIMIENTO NETO DE PRÉSTAMOS";
                    $Indicador->forindicador = "Ingresos por cartera / Cartera Por Vencer Promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R1, 2);
                    $Indicador->metindicador = ">10%";
                    $Indicador->save();
                    $R2 = (BalanceMensual::interesesydescuentosdeinversionesentitulosvalores($anio, $id) / ((BalanceMensual::inversiones($anio, $id) + BalanceMensual::inversiones($anio - 1, $id)) / 2)) * 100;
                    $val = BalanceMensual::interesesydescuentosdeinversionesentitulosvalores($anio, $id);
                    $val2 = ((BalanceMensual::inversiones($anio, $id) + BalanceMensual::inversiones($anio - 1, $id)) / 2);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "R2";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "RENDIMIENTO NETO DE INVERSIONES";
                    $Indicador->forindicador = "Ingresos por inversiones / Inversiones Promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R2, 2);
                    $Indicador->metindicador = "Tasa del rendimiento";
                    $Indicador->save();
                    $R3 = (BalanceMensual::interesesydescuentosganados($anio, $id) / (($Carteraporvencer + BalanceMensual::inversiones($anio - 1, $id) + $Carteraporvencer + BalanceMensual::inversiones($anio, $id)) / 2)) * 100;
                    $val = BalanceMensual::interesesydescuentosganados($anio, $id);
                    $val2 = (($Carteraporvencer + BalanceMensual::inversiones($anio - 1, $id) + $Carteraporvencer + BalanceMensual::inversiones($anio, $id)) / 2);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "R3";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "RENDIMIENTO DE ACTIVOS PRODUCTIVOS";
                    $Indicador->forindicador = "ITotal Intereses ganados / Activo produtivo promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R3, 2);
                    $Indicador->metindicador = ">18%";
                    $Indicador->save();
                    $R4 = (BalanceMensual::obligacionesconelpublico($anio, $id) / ((BalanceMensual::obligacionesconelpublico($anio - 1, $id) + BalanceMensual::obligacionesconelpublico($anio, $id)) / 2)) * 100;
                    $val = BalanceMensual::obligacionesconelpublico($anio, $id);
                    $val2 = ((BalanceMensual::obligacionesconelpublico($anio - 1, $id) + BalanceMensual::obligacionesconelpublico($anio, $id)) / 2);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "R4";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "COSTO FINANCIERO AHORROS DE SOCIOS";
                    $Indicador->forindicador = "Intereses pagados por depositos / Depositos de Socios Promedio ";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R4, 2);
                    $Indicador->metindicador = "5%";
                    $Indicador->save();
                    $R5 = (BalanceMensual::depositosdeahorro($anio, $id) / ((BalanceMensual::depositosalavista($anio - 1, $id) + BalanceMensual::depositosalavista($anio, $id)) / 2)) * 100;
                    $val = BalanceMensual::depositosdeahorro($anio, $id);
                    $val2 = ((BalanceMensual::depositosalavista($anio - 1, $id) + BalanceMensual::depositosalavista($anio, $id)) / 2);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "R5";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "COSTO FINANCIERO AHORROS A LA VISTA";
                    $Indicador->forindicador = "Intereses pagados por ahorros a la vista / Ahorros a la vista Promedio ";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R5, 2);
                    $Indicador->metindicador = "<=2%";
                    $Indicador->save();
                    $R6 = (BalanceMensual::depositosaplazo($anio, $id) / ((BalanceMensual::depositosaplazo2($anio - 1, $id) + BalanceMensual::depositosaplazo2($anio, $id)) / 2)) * 100;
                    $val = BalanceMensual::depositosaplazo($anio, $id);
                    $val2 = ((BalanceMensual::depositosaplazo2($anio - 1, $id) + BalanceMensual::depositosaplazo2($anio, $id)) / 2);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "R6";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "COSTO FINANCIERO DEPÓSITOS A PLAZO";
                    $Indicador->forindicador = "Intereses pagados por depositos a plazo / Depositos a plazo Promedio  ";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R6, 2);
                    $Indicador->metindicador = ">=5%";
                    $Indicador->save();
                    $R7 = (BalanceMensual::obligacionesfinancieras4103($anio, $id) / ((BalanceMensual::obligacionesfinancieras($anio - 1, $id) + BalanceMensual::obligacionesfinancieras($anio, $id)) / 2)) * 100;
                    $val = BalanceMensual::obligacionesfinancieras4103($anio, $id);
                    $val2 = ((BalanceMensual::obligacionesfinancieras($anio - 1, $id) + BalanceMensual::obligacionesfinancieras($anio, $id)) / 2);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "R7";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "COSTO FINANCIERO OBLIGACIONES FINANCIERAS";
                    $Indicador->forindicador = "Intereses pagados por obligaciones financieras / Obligaciones financieras Promedio ";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R7, 2);
                    $Indicador->metindicador = ">=10%";
                    $Indicador->save();
                    $R8 = (BalanceMensual::interesescausados($anio, $id) / (((BalanceMensual::totalpasivo($anio - 1, $id) - $Pasivosconcosto) + (BalanceMensual::totalpasivo($anio, $id) - $Pasivosconcosto)) / 2)) * 100;
                    $val = BalanceMensual::interesescausados($anio, $id);
                    $val2 = (((BalanceMensual::totalpasivo($anio - 1, $id) - $Pasivosconcosto) + (BalanceMensual::totalpasivo($anio, $id) - $Pasivosconcosto)) / 2);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "R8";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "COSTO FINANCIERO TOTAL";
                    $Indicador->forindicador = "Total intereses pagados / Pasivos con costo promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R8, 2);
                    $Indicador->metindicador = "<=5%";
                    $Indicador->save();
                    $R9 = (BalanceMensual::interesesydescuentosganados($anio, $id) / BalanceMensual::interesescausados($anio, $id)) * 100;
                    $val = BalanceMensual::interesesydescuentosganados($anio, $id);
                    $val2 = BalanceMensual::interesescausados($anio, $id);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "R9";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "MARGEN BRUTO ";
                    $Indicador->forindicador = "Total intereses ganados / Total intereses pagados";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R9, 2);
                    $Indicador->metindicador = "300%";
                    $Indicador->save();
                    $R10 = (BalanceMensual::gastosoperacion($anio, $id) / ((BalanceMensual::interesesydescuentosganados($anio, $id) - BalanceMensual::interesescausados($anio, $id)) + BalanceMensual::ingresosporservicios($anio, $id) - $Proviciones)) * 100;
                    $val = BalanceMensual::gastosoperacion($anio, $id);
                    $val2 = ((BalanceMensual::interesesydescuentosganados($anio, $id) - BalanceMensual::interesescausados($anio, $id)) + BalanceMensual::ingresosporservicios($anio, $id) - $Proviciones);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "R10";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "GRADO DE ABSORCIÓN";
                    $Indicador->forindicador = "Gastos de operación / (Margen bruto financiero - Provisiones)";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R10, 2);
                    $Indicador->metindicador = "<100%";
                    $Indicador->save();
                    $R11 = (BalanceMensual::gastosoperacion($anio, $id) / ((BalanceMensual::totalactivo($anio - 1, $id) + BalanceMensual::totalactivo($anio, $id)) / 2)) * 100;
                    $val = BalanceMensual::gastosoperacion($anio, $id);
                    $val2 = ((BalanceMensual::totalactivo($anio - 1, $id) + BalanceMensual::totalactivo($anio, $id)) / 2);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "R11";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "TASA EFICIENCIA DEL ACTIVO";
                    $Indicador->forindicador = "Gastos de operación / Activo total promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R11, 2);
                    $Indicador->metindicador = "<5%";
                    $Indicador->save();
                    $R12 = (BalanceMensual::gastosdepersonal($anio, $id) / ((BalanceMensual::totalactivo($anio - 1, $id) + BalanceMensual::totalactivo($anio, $id)) / 2)) * 100;
                    $val = BalanceMensual::gastosdepersonal($anio, $id);
                    $val2 = ((BalanceMensual::totalactivo($anio - 1, $id) + BalanceMensual::totalactivo($anio, $id)) / 2);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "R12";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "TASA EFICIENCIA GASTOS DE PERSONAL";
                    $Indicador->forindicador = "Gastos de personal / Activo total promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R12, 2);
                    $Indicador->metindicador = "<3%";
                    $Indicador->save();
                    $R13 = ($Proviciones / (((BalanceMensual::carteraporvencer($anio - 1, $id) + BalanceMensual::carteraquenodevengaintereses($anio - 1, $id) + BalanceMensual::carteravencida($anio - 1, $id) + BalanceMensual::carteraquenodevengaintereses($anio - 1, $id) + BalanceMensual::carteravencida($anio - 1, $id)) + (BalanceMensual::carteraporvencer($anio, $id) + BalanceMensual::carteraquenodevengaintereses($anio, $id) + BalanceMensual::carteravencida($anio, $id) + BalanceMensual::carteraquenodevengaintereses($anio, $id) + BalanceMensual::carteravencida($anio, $id))) / 2)) * 100;
                    $val = $Proviciones;
                    $val2 = (((BalanceMensual::carteraporvencer($anio - 1, $id) + BalanceMensual::carteraquenodevengaintereses($anio - 1, $id) + BalanceMensual::carteravencida($anio - 1, $id) + BalanceMensual::carteraquenodevengaintereses($anio - 1, $id) + BalanceMensual::carteravencida($anio - 1, $id)) + (BalanceMensual::carteraporvencer($anio, $id) + BalanceMensual::carteraquenodevengaintereses($anio, $id) + BalanceMensual::carteravencida($anio, $id) + BalanceMensual::carteraquenodevengaintereses($anio, $id) + BalanceMensual::carteravencida($anio, $id))) / 2);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "R13";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "TASA DE PROVISIONES";
                    $Indicador->forindicador = "Gasto de provisiones / Cartera bruta promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R13, 2);
                    $Indicador->metindicador = "<2%";
                    $Indicador->save();
                    $R14 = (BalanceMensual::perdidasyganancias($anio, $id) / ((BalanceMensual::totalactivo($anio - 1, $id) + BalanceMensual::totalactivo($anio, $id)) / 2)) * 100;
                    $val = BalanceMensual::perdidasyganancias($anio, $id);
                    $val2 = ((BalanceMensual::totalactivo($anio - 1, $id) + BalanceMensual::totalactivo($anio, $id)) / 2);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "R14";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "RENDIMIENTO DEL ACTIVO TOTAL";
                    $Indicador->forindicador = "Resultado del ejercicio / Activo  promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R14, 2);
                    $Indicador->metindicador = "No inflación";
                    $Indicador->save();
                    $R15 = (BalanceMensual::perdidasyganancias($anio, $id) / ((BalanceMensual::totalpatrimonio($anio - 1, $id) + BalanceMensual::totalpatrimonio($anio, $id)) / 2)) * 100;
                    $val = BalanceMensual::perdidasyganancias($anio, $id);
                    $val2 = ((BalanceMensual::totalpatrimonio($anio - 1, $id) + BalanceMensual::totalpatrimonio($anio, $id)) / 2);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "R15";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "RENDIMIENTO DEL PATRIMONIO";
                    $Indicador->forindicador = "Resultado del ejercicio / Patrimonio promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R15, 2);
                    $Indicador->metindicador = ">10%";
                    $Indicador->save();
                    $R16 = (BalanceMensual::perdidasyganancias($anio, $id) / ((BalanceMensual::capitalsocial($anio - 1, $id) + BalanceMensual::capitalsocial($anio, $id)) / 2)) * 100;
                    $val = BalanceMensual::perdidasyganancias($anio, $id);
                    $val2 = ((BalanceMensual::capitalsocial($anio - 1, $id) + BalanceMensual::capitalsocial($anio, $id)) / 2);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "R16";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "RENDIMIENTO DE CERTIFICADOS DE APORTACIÓN";
                    $Indicador->forindicador = "Resultado del ejercicio / Certificados de aportacion  promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R16, 2);
                    $Indicador->metindicador = ">12%";
                    $Indicador->save();

                    $L1 = (BalanceMensual::fondosdisponibles($anio, $id) / $Totaldepositoscortoplazo) * 100;
                    $val = BalanceMensual::fondosdisponibles($anio, $id);
                    $val2 = $Totaldepositoscortoplazo;
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "L1";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "LIQUIDEZ CORRIENTE";
                    $Indicador->forindicador = "Fondos disponibles / Depositos de socios a corto plazo";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($L1, 2);
                    $Indicador->metindicador = "15% - 20%";
                    $Indicador->save();
                    $L2 = ((BalanceMensual::fondosdisponibles($anio, $id) + BalanceMensual::inversiones($anio, $id)) / BalanceMensual::depositosdesocios($anio, $id)) * 100;
                    $val = BalanceMensual::fondosdisponibles($anio, $id);
                    $val11 = BalanceMensual::inversiones($anio, $id);
                    $val2 = BalanceMensual::depositosdesocios($anio, $id);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "L2";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "LIQUIDEZ GENERAL";
                    $Indicador->forindicador = "Fondos disponibles + Inversiones / Depositos de socios";
                    $Indicador->calindicador = "($val + $val11) / $val2";
                    $Indicador->valorindicador = round($L2, 2);
                    $Indicador->metindicador = "20%";
                    $Indicador->save();

                    $A1 = ($Carteravencida / $Carterabruta) * 100;
                    $val = $Carteravencida;
                    $val2 = $Carterabruta;
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "A1";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "INDICADOR DE CARTERA VENCIDA";
                    $Indicador->forindicador = "Total Cartera Vencida / Total Cartera Bruta";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($A1, 2);
                    $Indicador->metindicador = "<=3%";
                    $Indicador->save();
                    $A2 = ($Carteraimproductiva / $Carterabruta) * 100;
                    $val = $Carteraimproductiva;
                    $val2 = $Carterabruta;
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "A2";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "INDICADOR DE MOROSIDAD AMPLIADA";
                    $Indicador->forindicador = "Total Cartera Improductiva / Total Cartera Bruta";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($A2, 2);
                    $Indicador->metindicador = "<=5%";
                    $Indicador->save();
                    $A3 = ($Totalactivosimproductivos / $Totalactivo) * 100;
                    $val = $Totalactivosimproductivos;
                    $val2 = $Totalactivo;
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "A3";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "INDICADOR DE ACTIVOS IMPRODUCTIVOS";
                    $Indicador->forindicador = "Activos Improductivos / Total Activo";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($A3, 2);
                    $Indicador->metindicador = "<=5%";
                    $Indicador->save();
                    $A4 = (($Totalpatrimonio + $Pasivossincosto) / $Totalactivosimproductivos) * 100;
                    $val = $Totalpatrimonio;
                    $val1 = $Pasivossincosto;
                    $val2 = $Totalactivosimproductivos;
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "A4";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "PROTECCIÓN DE ACTIVOS IMPRODUCTIVOS";
                    $Indicador->forindicador = "(Patrimonio + Pasivos sin costo) / Activos Improductivos";
                    $Indicador->calindicador = "($val + $val1) / $val2";
                    $Indicador->valorindicador = round($A4, 2);
                    $Indicador->metindicador = ">=200%";
                    $Indicador->save();

                    $S1 = (BalanceMensual::fondosdisponibles($anio, $id) / BalanceMensual::fondosdisponibles($anio - 1, $id) - 1) * 100;
                    $val = BalanceMensual::fondosdisponibles($anio, $id);
                    $val2 = BalanceMensual::fondosdisponibles($anio - 1, $id);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "S1";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE FONDOS DISPONIBLES";
                    $Indicador->forindicador = "(Saldo actual de fondos disponibles / Saldo fondos disponibles periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S1, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S2 = ($Carterabruta / (BalanceMensual::carteraporvencer($anio, $id) + BalanceMensual::carteraquenodevengaintereses($anio, $id) + BalanceMensual::carteravencida($anio, $id) + BalanceMensual::proviciones($anio - 1, $id)) - 1) * 100;
                    $val = $Carterabruta;
                    $val2 = (BalanceMensual::carteraporvencer($anio, $id) + BalanceMensual::carteraquenodevengaintereses($anio, $id) + BalanceMensual::carteravencida($anio, $id) + BalanceMensual::proviciones($anio - 1, $id));
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "S2";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE PRESTAMOS";
                    $Indicador->forindicador = "(Saldo actual de cartera / Saldo cartera periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S2, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S3 = (BalanceMensual::obligacionesconelpublico($anio, $id) / BalanceMensual::obligacionesconelpublico($anio - 1, $id) - 1) * 100;
                    $val = BalanceMensual::obligacionesconelpublico($anio, $id);
                    $val2 = BalanceMensual::obligacionesconelpublico($anio - 1, $id);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "S3";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE DEPOSITOS DE SOCIOS";
                    $Indicador->forindicador = "(Saldo actual de depositos de socios / Saldo depositos de socios periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S3, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S4 = (BalanceMensual::depositosalavista($anio, $id) / BalanceMensual::depositosalavista($anio - 1, $id) - 1) * 100;
                    $val = BalanceMensual::depositosalavista($anio, $id);
                    $val2 = BalanceMensual::depositosalavista($anio - 1, $id);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "S4";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE AHORROS A LA VISTA";
                    $Indicador->forindicador = "(Saldo actual de ahorros a la vista / Saldo ahorros a la vista periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S4, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S5 = (BalanceMensual::depositosaplazo2($anio, $id) / BalanceMensual::depositosaplazo2($anio - 1, $id) - 1) * 100;
                    $val = BalanceMensual::depositosaplazo2($anio, $id);
                    $val2 = BalanceMensual::depositosaplazo2($anio - 1, $id);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "S5";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE DEPOSITOS A PLAZO";
                    $Indicador->forindicador = "(Saldo actual de depositos a plazo / Saldo depositos a plazo periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S5, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S6 = (BalanceMensual::obligacionesfinancieras($anio, $id) / BalanceMensual::obligacionesfinancieras($anio - 1, $id) - 1) * 100;
                    $val = BalanceMensual::obligacionesfinancieras($anio, $id);
                    $val2 = BalanceMensual::obligacionesfinancieras($anio - 1, $id);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "S6";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE OBLIGACIONES FINANCIERAS";
                    $Indicador->forindicador = "(Saldo actual de obligaciones financieras / Saldo obligaciones financieras periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S6, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S7 = (BalanceMensual::capitalsocial($anio, $id) / BalanceMensual::capitalsocial($anio - 1, $id) - 1) * 100;
                    $val = BalanceMensual::capitalsocial($anio, $id);
                    $val2 = BalanceMensual::capitalsocial($anio - 1, $id);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "S7";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE CERTIFICADOS DE APORTACION";
                    $Indicador->forindicador = "(Saldo actual de certificados de aportacion / Saldo certificados de aportacion periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S7, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S8 = (BalanceMensual::totalpatrimonio($anio, $id) / BalanceMensual::totalpatrimonio($anio - 1, $id) - 1) * 100;
                    $val = BalanceMensual::totalpatrimonio($anio, $id);
                    $val2 = BalanceMensual::totalpatrimonio($anio - 1, $id);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "S8";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DEL PATRIMONIO";
                    $Indicador->forindicador = "(Saldo actual de patrimonio / Saldo patrimonio periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S8, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S9 = (BalanceMensual::totalactivo($anio, $id) / BalanceMensual::totalactivo($anio - 1, $id) - 1) * 100;
                    $val = BalanceMensual::totalactivo($anio, $id);
                    $val2 = BalanceMensual::totalactivo($anio - 1, $id);
                    $Indicador = new IndicadoresMensual;
                    $Indicador->codindicador = "S9";
                    $Indicador->id = $id;
                    $Indicador->codaniomes = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DEL NÚMERO DE SOCIOS";
                    $Indicador->forindicador = "(Total número de socios actual / Saldo número de socios periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S9, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S10 = 0;
                } else {

                    $Carteraporvencer = BalanceMensual::carteraporvencer($anio, $id);
                    $Carteraquenodevengaintereses = BalanceMensual::carteraquenodevengaintereses($anio, $id);
                    $Carteravencida = BalanceMensual::carteravencida($anio, $id);
                    $Carteraimproductiva = $Carteraquenodevengaintereses + $Carteravencida;
                    $Carterabruta = $Carteraporvencer + $Carteraimproductiva;
                    $Proviciones =  BalanceMensual::proviciones($anio, $id);
                    $Carteraneta = $Carterabruta + $Proviciones;

                    $Cuentasporcobrar = BalanceMensual::cuentasporcobrar($anio, $id);
                    $Bienesendaciondepago = BalanceMensual::bienesendaciondepago($anio, $id);
                    $Activosfijos = BalanceMensual::activosfijos($anio, $id);
                    $Otrosactivos = BalanceMensual::otrosactivos($anio, $id);
                    $Totalactivosimproductivos = $Cuentasporcobrar + $Bienesendaciondepago + $Activosfijos + $Otrosactivos + $Carteraimproductiva;

                    $Depositosdesocios = BalanceMensual::depositosdesocios($anio, $id);
                    $Obligacionesfinancieras = BalanceMensual::obligacionesfinancieras($anio, $id);
                    $Pasivosconcosto = $Depositosdesocios + $Obligacionesfinancieras;

                    $Reservastotales = BalanceMensual::reservastotales($anio, $id);

                    $Depositosalavista = BalanceMensual::depositosalavista($anio, $id);
                    $de1a30dias = BalanceMensual::de1a30dias($anio, $id);
                    $de31a90dias = BalanceMensual::de31a90dias($anio, $id);
                    $oblde1a30dias = BalanceMensual::oblde1a30dias($anio, $id);
                    $oblde31a90dias = BalanceMensual::oblde31a90dias($anio, $id);

                    $Totaldepositoscortoplazo = $Depositosalavista + $de1a30dias + $de31a90dias + $oblde1a30dias + $oblde31a90dias;

                    $Activoproductivo = $Carteraporvencer + BalanceMensual::inversiones($anio, $id);
                    $Pasivossincosto =  BalanceMensual::totalpasivo($anio, $id) - $Pasivosconcosto;

                    $Totalactivo = BalanceMensual::totalactivo($anio, $id);
                    $Totalpasivo = BalanceMensual::totalpasivo($anio, $id);
                    $Totalpatrimonio = BalanceMensual::totalpatrimonio($anio, $id);

                    $P1 = - ($Proviciones / $Carteravencida) * 100;
                    $Indicador = IndicadoresMensual::Indicador("P1", $anio, $id);
                    $Indicador->valorindicador = round($P1, 2);
                    $Indicador->save();
                    $P2 = - ($Proviciones / $Carteraimproductiva) * 100;
                    $Indicador = IndicadoresMensual::Indicador("P2", $anio, $id);
                    $Indicador->valorindicador = round($P2, 2);
                    $Indicador->save();
                    $P3 = ((($Totalactivo + BalanceMensual::proviciones($anio, $id)) - ($Totalactivosimproductivos +  $Totalpasivo -  $Depositosdesocios)) / ($Totalpatrimonio + $Depositosdesocios)) * 100;
                    $Indicador = IndicadoresMensual::Indicador("P3", $anio, $id);
                    $Indicador->valorindicador = round($P3, 2);
                    $Indicador->save();
                    $P4 = ($Totalpatrimonio / $Totalactivo) * 100;
                    $Indicador = IndicadoresMensual::Indicador("P4", $anio, $id);
                    $Indicador->valorindicador = round($P4, 2);
                    $Indicador->save();

                    $E1 = ($Carteraneta / $Totalactivo) * 100;
                    $Indicador = IndicadoresMensual::Indicador("E1", $anio, $id);
                    $Indicador->valorindicador = round($E1, 2);
                    $Indicador->save();
                    $E2 = (BalanceMensual::inversiones($anio, $id) / $Totalactivo) * 100;
                    $Indicador = IndicadoresMensual::Indicador("E2", $anio, $id);
                    $Indicador->valorindicador = round($E2, 2);
                    $Indicador->save();
                    $E3 = ($Depositosdesocios / $Totalactivo) * 100;
                    $Indicador = IndicadoresMensual::Indicador("E3", $anio, $id);
                    $Indicador->valorindicador = round($E3, 2);
                    $Indicador->save();
                    $E4 = ($Obligacionesfinancieras / $Totalactivo) * 100;
                    $Indicador = IndicadoresMensual::Indicador("E4", $anio, $id);
                    $Indicador->valorindicador = round($E4, 2);
                    $Indicador->save();
                    $E5 = (BalanceMensual::capitalsocial($anio, $id) / $Totalactivo) * 100;
                    $Indicador = IndicadoresMensual::Indicador("E5", $anio, $id);
                    $Indicador->valorindicador = round($E5, 2);
                    $Indicador->save();
                    $E6 = ($Reservastotales / $Totalactivo) * 100;
                    $Indicador = IndicadoresMensual::Indicador("E6", $anio, $id);
                    $Indicador->valorindicador = round($E6, 2);
                    $Indicador->save();

                    $R1 = ((BalanceMensual::interesesydescuentosdeinversionesentitulosvalores($anio, $id) + BalanceMensual::interesesydescuentosdecarteradecreditos($anio, $id)) / ((BalanceMensual::carteraporvencer($anio, $id) + BalanceMensual::carteraporvencer($anio - 1, $id)) / 2)) * 100;
                    $Indicador = IndicadoresMensual::Indicador("R1", $anio, $id);
                    $Indicador->valorindicador = round($R1, 2);
                    $Indicador->save();
                    $R2 = (BalanceMensual::interesesydescuentosdeinversionesentitulosvalores($anio, $id) / ((BalanceMensual::inversiones($anio, $id) + BalanceMensual::inversiones($anio - 1, $id)) / 2)) * 100;
                    $Indicador = IndicadoresMensual::Indicador("R2", $anio, $id);
                    $Indicador->valorindicador = round($R2, 2);
                    $Indicador->save();
                    $R3 = (BalanceMensual::interesesydescuentosganados($anio, $id) / (($Carteraporvencer + BalanceMensual::inversiones($anio - 1, $id) + $Carteraporvencer + BalanceMensual::inversiones($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresMensual::Indicador("R3", $anio, $id);
                    $Indicador->valorindicador = round($R3, 2);
                    $Indicador->save();
                    $R4 = (BalanceMensual::obligacionesconelpublico($anio, $id) / ((BalanceMensual::obligacionesconelpublico($anio - 1, $id) + BalanceMensual::obligacionesconelpublico($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresMensual::Indicador("R4", $anio, $id);
                    $Indicador->valorindicador = round($R4, 2);
                    $Indicador->save();
                    $R5 = (BalanceMensual::depositosdeahorro($anio, $id) / ((BalanceMensual::depositosalavista($anio - 1, $id) + BalanceMensual::depositosalavista($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresMensual::Indicador("R5", $anio, $id);
                    $Indicador->valorindicador = round($R5, 2);
                    $Indicador->save();
                    $R6 = (BalanceMensual::depositosaplazo($anio, $id) / ((BalanceMensual::depositosaplazo2($anio - 1, $id) + BalanceMensual::depositosaplazo2($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresMensual::Indicador("R6", $anio, $id);
                    $Indicador->valorindicador = round($R6, 2);
                    $Indicador->save();
                    $R7 = (BalanceMensual::obligacionesfinancieras4103($anio, $id) / ((BalanceMensual::obligacionesfinancieras($anio - 1, $id) + BalanceMensual::obligacionesfinancieras($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresMensual::Indicador("R7", $anio, $id);
                    $Indicador->valorindicador = round($R7, 2);
                    $Indicador->save();
                    $R8 = (BalanceMensual::interesescausados($anio, $id) / (((BalanceMensual::totalpasivo($anio - 1, $id) - $Pasivosconcosto) + (BalanceMensual::totalpasivo($anio, $id) - $Pasivosconcosto)) / 2)) * 100;
                    $Indicador = IndicadoresMensual::Indicador("R8", $anio, $id);
                    $Indicador->valorindicador = round($R8, 2);
                    $Indicador->save();
                    $R9 = (BalanceMensual::interesesydescuentosganados($anio, $id) / BalanceMensual::interesescausados($anio, $id)) * 100;
                    $Indicador = IndicadoresMensual::Indicador("R9", $anio, $id);
                    $Indicador->valorindicador = round($R9, 2);
                    $Indicador->save();
                    $R10 = (BalanceMensual::gastosoperacion($anio, $id) / ((BalanceMensual::interesesydescuentosganados($anio, $id) - BalanceMensual::interesescausados($anio, $id)) + BalanceMensual::ingresosporservicios($anio, $id) - $Proviciones)) * 100;
                    $Indicador = IndicadoresMensual::Indicador("R10", $anio, $id);
                    $Indicador->valorindicador = round($R10, 2);
                    $Indicador->save();
                    $R11 = (BalanceMensual::gastosoperacion($anio, $id) / ((BalanceMensual::totalactivo($anio - 1, $id) + BalanceMensual::totalactivo($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresMensual::Indicador("R11", $anio, $id);
                    $Indicador->valorindicador = round($R11, 2);
                    $Indicador->save();
                    $R12 = (BalanceMensual::gastosdepersonal($anio, $id) / ((BalanceMensual::totalactivo($anio - 1, $id) + BalanceMensual::totalactivo($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresMensual::Indicador("R12", $anio, $id);
                    $Indicador->valorindicador = round($R12, 2);
                    $Indicador->save();
                    $R13 = ($Proviciones / (((BalanceMensual::carteraporvencer($anio - 1, $id) + BalanceMensual::carteraquenodevengaintereses($anio - 1, $id) + BalanceMensual::carteravencida($anio - 1, $id) + BalanceMensual::carteraquenodevengaintereses($anio - 1, $id) + BalanceMensual::carteravencida($anio - 1, $id)) + (BalanceMensual::carteraporvencer($anio, $id) + BalanceMensual::carteraquenodevengaintereses($anio, $id) + BalanceMensual::carteravencida($anio, $id) + BalanceMensual::carteraquenodevengaintereses($anio, $id) + BalanceMensual::carteravencida($anio, $id))) / 2)) * 100;
                    $Indicador = IndicadoresMensual::Indicador("R13", $anio, $id);
                    $Indicador->valorindicador = round($R13, 2);
                    $Indicador->save();
                    $R14 = (BalanceMensual::perdidasyganancias($anio, $id) / ((BalanceMensual::totalactivo($anio - 1, $id) + BalanceMensual::totalactivo($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresMensual::Indicador("R14", $anio, $id);
                    $Indicador->valorindicador = round($R14, 2);
                    $Indicador->save();
                    $R15 = (BalanceMensual::perdidasyganancias($anio, $id) / ((BalanceMensual::totalpatrimonio($anio - 1, $id) + BalanceMensual::totalpatrimonio($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresMensual::Indicador("R15", $anio, $id);
                    $Indicador->valorindicador = round($R15, 2);
                    $Indicador->save();
                    $R16 = (BalanceMensual::perdidasyganancias($anio, $id) / ((BalanceMensual::capitalsocial($anio - 1, $id) + Balance::capitalsocial($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresMensual::Indicador("R16", $anio, $id);
                    $Indicador->valorindicador = round($R16, 2);
                    $Indicador->save();

                    $L1 = (BalanceMensual::fondosdisponibles($anio, $id) / $Totaldepositoscortoplazo) * 100;
                    $Indicador = IndicadoresMensual::Indicador("L1", $anio, $id);
                    $Indicador->valorindicador = round($L1, 2);
                    $Indicador->save();
                    $L2 = ((BalanceMensual::fondosdisponibles($anio, $id) + BalanceMensual::inversiones($anio, $id)) / BalanceMensual::depositosdesocios($anio, $id)) * 100;
                    $Indicador = IndicadoresMensual::Indicador("L2", $anio, $id);
                    $Indicador->valorindicador = round($L2, 2);
                    $Indicador->save();

                    $A1 = ($Carteravencida / $Carterabruta) * 100;
                    $Indicador = IndicadoresMensual::Indicador("A1", $anio, $id);
                    $Indicador->valorindicador = round($A1, 2);
                    $Indicador->save();
                    $A2 = ($Carteraimproductiva / $Carterabruta) * 100;
                    $Indicador = IndicadoresMensual::Indicador("A2", $anio, $id);
                    $Indicador->valorindicador = round($A2, 2);
                    $Indicador->save();
                    $A3 = ($Totalactivosimproductivos / $Totalactivo) * 100;
                    $Indicador = IndicadoresMensual::Indicador("A3", $anio, $id);
                    $Indicador->valorindicador = round($A3, 2);
                    $Indicador->save();
                    $A4 = (($Totalpatrimonio + $Pasivossincosto) / $Totalactivosimproductivos) * 100;
                    $Indicador = IndicadoresMensual::Indicador("A4", $anio, $id);
                    $Indicador->valorindicador = round($A4, 2);
                    $Indicador->save();

                    $S1 = (BalanceMensual::fondosdisponibles($anio, $id) / BalanceMensual::fondosdisponibles($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresMensual::Indicador("S1", $anio, $id);
                    $Indicador->valorindicador = round($S1, 2);
                    $Indicador->save();
                    $S2 = ($Carterabruta / (BalanceMensual::carteraporvencer($anio, $id) + BalanceMensual::carteraquenodevengaintereses($anio, $id) + BalanceMensual::carteravencida($anio, $id) + BalanceMensual::proviciones($anio - 1, $id)) - 1) * 100;
                    $Indicador = IndicadoresMensual::Indicador("S2", $anio, $id);
                    $Indicador->valorindicador = round($S2, 2);
                    $Indicador->save();
                    $S3 = (BalanceMensual::obligacionesconelpublico($anio, $id) / BalanceMensual::obligacionesconelpublico($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresMensual::Indicador("S3", $anio, $id);
                    $Indicador->valorindicador = round($S3, 2);
                    $Indicador->save();
                    $S4 = (BalanceMensual::depositosalavista($anio, $id) / BalanceMensual::depositosalavista($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresMensual::Indicador("S4", $anio, $id);
                    $Indicador->valorindicador = round($S4, 2);
                    $Indicador->save();
                    $S5 = (BalanceMensual::depositosaplazo2($anio, $id) / BalanceMensual::depositosaplazo2($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresMensual::Indicador("S5", $anio, $id);
                    $Indicador->valorindicador = round($S5, 2);
                    $Indicador->save();
                    $S6 = (BalanceMensual::obligacionesfinancieras($anio, $id) / BalanceMensual::obligacionesfinancieras($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresMensual::Indicador("S6", $anio, $id);
                    $Indicador->valorindicador = round($S6, 2);
                    $Indicador->save();
                    $S7 = (BalanceMensual::capitalsocial($anio, $id) / BalanceMensual::capitalsocial($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresMensual::Indicador("S7", $anio, $id);
                    $Indicador->valorindicador = round($S7, 2);
                    $Indicador->save();
                    $S8 = (BalanceMensual::totalpatrimonio($anio, $id) / BalanceMensual::totalpatrimonio($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresMensual::Indicador("S8", $anio, $id);
                    $Indicador->valorindicador = round($S8, 2);
                    $Indicador->save();
                    $S9 = (BalanceMensual::totalactivo($anio, $id) / BalanceMensual::totalactivo($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresMensual::Indicador("S9", $anio, $id);
                    $Indicador->valorindicador = round($S9, 2);
                    $Indicador->save();
                    $S10 = 0;
                }
            }

            $Anios = BalanceMensual::listaranios($id);
            return view('administrador/administradorIndicadoresMensual', compact('Anios'));
        } else {
            return view('auth/login');
        }
    }

    public function indicadoresm(Request $request, $codaniomes)
    {
        $id = Auth::id();
        $Resumen = [];

        if ($request->ajax()) {
            $Indicadores = IndicadoresMensual::Indicadores($codaniomes, $id);
            foreach ($Indicadores as $indi) {
                $Indicadoresanioanterior = IndicadoresMensual::Indicador($indi['codindicador'], $codaniomes - 1, $id);
                $Resumen = array(
                    "codindicador" => $indi['codindicador'],
                    "nomindicador" => $indi['nomindicador'],
                    "forindicador" => $indi['forindicador'],
                    "calindicador" => $indi['calindicador'],
                    "metindicador" => $indi['metindicador'],
                    "nomanio" => $indi['nomanio'],
                    "nommes" => $indi['nommes'],
                    "valorindicador" => $indi['valorindicador'],
                    "nomanio2" => $Indicadoresanioanterior->nomanio,
                    "nommes2" => $Indicadoresanioanterior->nommes,
                    "forindicador2" => $Indicadoresanioanterior->forindicador,
                    "calindicador2" => $Indicadoresanioanterior->calindicador,
                    "valorindicador2" => $Indicadoresanioanterior->valorindicador,
                );
                $Resultado[] = $Resumen;
            }
            return response()->json($Resultado);
        }
    }

    public function BalanceSemestral()
    {
        if (Auth::check()) {
            $Anios = Anio::all();
            $Semestres = Semestre::all();
            return view('administrador/administradorBalanceSemestral', compact('Anios', 'Semestres'));
        } else {
            return view('auth/login');
        }
    }

    public function Semestre(Request $request, $codanio)
    {
        if ($request->ajax()) {
            $AnioSemestre = AnioSemestre::AnioSemestre($codanio);
            return response()->json($AnioSemestre);
        }
    }

    public function conosinbalancesemestral(Request $request, $codaniosemestre)
    {
        $id = Auth::id();
        if ($request->ajax()) {
            $BalanceSemestral = BalanceSemestral::BalanceSemestral($codaniosemestre, $id);
            return response()->json($BalanceSemestral);
        }
    }

    public function excelsemestral(Request $request)
    {
        $balance = $request->file('balance');
        $codanio = $request->semestre;
        $id = Auth::id();
        Excel::import(new BalanceSemestralImport($codanio, $id), $balance);
        return back();
    }

    public function eliminarbalancesemestral(Request $request)
    {
        $id = Auth::id();
        $codanio = $request->anio;
        BalanceSemestral::eliminarbalancesemestral($codanio, $id);
        return response()->json(['success' => 'El balance se eliminó']);
    }

    public function actualizarbalancesemestral(Request $request)
    {
        $id = Auth::id();
        $filas = json_decode(json_encode($_POST['valores']), True);
        foreach ($filas as $fila) {
            $valorbalance =  $fila['valorbalance'];
            $codanio = $fila['anio'];
            $codcontable = $fila['codcontable'];
            $balanceanual = BalanceSemestral::balance($codanio, $id, $codcontable);
            $balanceanual->valorbalance = $valorbalance;
            $balanceanual->save();
        }

        return response()->json(['success' => 'El balance se actualizó']);
    }

    public function ResumenBalanceSemestral()
    {
        if (Auth::check()) {
            $Anios = Anio::all();
            $Semestres = Semestre::all();
            return view('administrador/administradorResumenBalanceSemestral', compact('Anios', 'Semestres'));
        } else {
            return view('auth/login');
        }
    }

    public function conosinresumensemestral($codaniosemestral)
    {
        $id = Auth::id();
        $Resumen = [];
        $Resultado = [];
        $anio = $codaniosemestral;
        $AnioMes = AnioSemestre::CodigoAnioSemestre($anio);
        $nomanio = $AnioMes->first()->nomanio;
        $nomsemestre = $AnioMes->first()->nomsemestre;
        $Carteraporvencer = BalanceSemestral::carteraporvencer($anio, $id);
        if ($Carteraporvencer > 0) {
            $Carteraporvencer = BalanceSemestral::carteraporvencer($anio, $id);
            $Carteraquenodevengaintereses = BalanceSemestral::carteraquenodevengaintereses($anio, $id);
            $Carteravencida = BalanceSemestral::carteravencida($anio, $id);
            $Carteraimproductiva = $Carteraquenodevengaintereses + $Carteravencida;
            $Carterabruta = $Carteraporvencer + $Carteraimproductiva;
            $Proviciones =  BalanceSemestral::proviciones($anio, $id);
            $Carteraneta = $Carterabruta + $Proviciones;

            $Cuentasporcobrar = BalanceSemestral::cuentasporcobrar($anio, $id);
            $Bienesendaciondepago = BalanceSemestral::bienesendaciondepago($anio, $id);
            $Activosfijos = BalanceSemestral::activosfijos($anio, $id);
            $Otrosactivos = BalanceSemestral::otrosactivos($anio, $id);
            $Totalactivosimproductivos = $Cuentasporcobrar + $Bienesendaciondepago + $Activosfijos + $Otrosactivos + $Carteraimproductiva;

            $Depositosdesocios = BalanceSemestral::depositosdesocios($anio, $id);
            $Obligacionesfinancieras = BalanceSemestral::obligacionesfinancieras($anio, $id);
            $Pasivosconcosto = $Depositosdesocios + $Obligacionesfinancieras;

            $Reservastotales = BalanceSemestral::reservastotales($anio, $id);

            $Depositosalavista = BalanceSemestral::depositosalavista($anio, $id);
            $de1a30dias = BalanceSemestral::de1a30dias($anio, $id);
            $de31a90dias = BalanceSemestral::de31a90dias($anio, $id);
            $oblde1a30dias = BalanceSemestral::oblde1a30dias($anio, $id);
            $oblde31a90dias = BalanceSemestral::oblde31a90dias($anio, $id);

            $Totaldepositoscortoplazo = $Depositosalavista + $de1a30dias + $de31a90dias + $oblde1a30dias + $oblde31a90dias;

            $Activoproductivo = $Carteraporvencer + BalanceSemestral::inversiones($anio, $id);
            $Pasivossincosto =  BalanceSemestral::totalpasivo($anio, $id) - $Pasivosconcosto;

            $Resumen = array(
                "Carteraporvencer" => round($Carteraporvencer, 2),
                "Carteraquenodevengaintereses" => round($Carteraquenodevengaintereses, 2),
                "Carteravencida" => round($Carteravencida, 2),
                "Carterabruta" => round($Carterabruta, 2),
                "Proviciones" => round($Proviciones, 2),
                "Carteraneta" => round($Carteraneta, 2),
                "Cuentasporcobrar" => round($Cuentasporcobrar, 2),
                "Bienesendaciondepago" => round($Bienesendaciondepago, 2),
                "Activosfijos" => round($Activosfijos, 2),
                "Otrosactivos" => round($Otrosactivos, 2),
                "Totalactivosimproductivos" => round($Totalactivosimproductivos, 2),
                "Depositosdesocios" => round($Depositosdesocios, 2),
                "Obligacionesfinancieras" => round($Obligacionesfinancieras, 2),
                "Pasivosconcosto" => round($Pasivosconcosto, 2),
                "Reservastotales" => round($Reservastotales, 2),
                "Depositosalavista" => round($Depositosalavista, 2),
                "de1a30dias" => round($de1a30dias, 2),
                "de31a90dias" => round($de31a90dias, 2),
                "oblde1a30dias" => round($oblde1a30dias, 2),
                "oblde31a90dias" => round($oblde31a90dias, 2),
                "Totaldepositoscortoplazo" => round($Totaldepositoscortoplazo, 2),
                "Activoproductivo" => round($Activoproductivo, 2),
                "Pasivossincosto" => round($Pasivossincosto, 2),
                "nomanio" => $nomanio,
                "nomsemestre" => $nomsemestre
            );
            $Resultado[] = $Resumen;
        } else {
        }
        return response()->json($Resultado);
    }

    public function IndicadoresSemestral()
    {
        if (Auth::check()) {
            $id = Auth::id();
            $Anios = BalanceSemestral::listaranios($id);

            $Resumen = [];
            foreach ($Anios as $codanio) {
                $anio = $codanio['codaniosemestral'];
                $nomanio = $codanio['nomanio'];
                $nomsemestre = $codanio['nomsemestre'];
                $Datos = IndicadoresSemestral::Existeindicadoressemestral($anio, $id);
                if (empty($Datos)) {
                    $Carteraporvencer = BalanceSemestral::carteraporvencer($anio, $id);
                    $Carteraquenodevengaintereses = BalanceSemestral::carteraquenodevengaintereses($anio, $id);
                    $Carteravencida = BalanceSemestral::carteravencida($anio, $id);
                    $Carteraimproductiva = $Carteraquenodevengaintereses + $Carteravencida;
                    $Carterabruta = $Carteraporvencer + $Carteraimproductiva;
                    $Proviciones =  BalanceSemestral::proviciones($anio, $id);
                    $Carteraneta = $Carterabruta + $Proviciones;

                    $Cuentasporcobrar = BalanceSemestral::cuentasporcobrar($anio, $id);
                    $Bienesendaciondepago = BalanceSemestral::bienesendaciondepago($anio, $id);
                    $Activosfijos = BalanceSemestral::activosfijos($anio, $id);
                    $Otrosactivos = BalanceSemestral::otrosactivos($anio, $id);
                    $Totalactivosimproductivos = $Cuentasporcobrar + $Bienesendaciondepago + $Activosfijos + $Otrosactivos + $Carteraimproductiva;

                    $Depositosdesocios = BalanceSemestral::depositosdesocios($anio, $id);
                    $Obligacionesfinancieras = BalanceSemestral::obligacionesfinancieras($anio, $id);
                    $Pasivosconcosto = $Depositosdesocios + $Obligacionesfinancieras;

                    $Reservastotales = BalanceSemestral::reservastotales($anio, $id);

                    $Depositosalavista = BalanceSemestral::depositosalavista($anio, $id);
                    $de1a30dias = BalanceSemestral::de1a30dias($anio, $id);
                    $de31a90dias = BalanceSemestral::de31a90dias($anio, $id);
                    $oblde1a30dias = BalanceSemestral::oblde1a30dias($anio, $id);
                    $oblde31a90dias = BalanceSemestral::oblde31a90dias($anio, $id);

                    $Totaldepositoscortoplazo = $Depositosalavista + $de1a30dias + $de31a90dias + $oblde1a30dias + $oblde31a90dias;

                    $Activoproductivo = $Carteraporvencer + BalanceSemestral::inversiones($anio, $id);
                    $Pasivossincosto =  BalanceSemestral::totalpasivo($anio, $id) - $Pasivosconcosto;

                    $Totalactivo = BalanceSemestral::totalactivo($anio, $id);
                    $Totalpasivo = BalanceSemestral::totalpasivo($anio, $id);
                    $Totalpatrimonio = BalanceSemestral::totalpatrimonio($anio, $id);

                    $P1 = - ($Proviciones / $Carteravencida) * 100;
                    $Indicador = new IndicadoresSemestral();
                    $Indicador->codindicador = "P1";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "PROTECCIÓN CARTERA VENCIDA";
                    $Indicador->forindicador = "Provision cartera / Cartera vencida";
                    $Indicador->calindicador = "$Proviciones / $Carteravencida";
                    $Indicador->valorindicador = round($P1, 2);
                    $Indicador->metindicador = "100%";
                    $Indicador->save();
                    $P2 = - ($Proviciones / $Carteraimproductiva) * 100;
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "P2";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "PROTECCIÓN CARTERA IMPRODUCTIVA";
                    $Indicador->forindicador = "Provision cartera / Cartera improductiva";
                    $Indicador->calindicador = "$Proviciones / $Carteraimproductiva";
                    $Indicador->valorindicador = round($P2, 2);
                    $Indicador->metindicador = "100%";
                    $Indicador->save();
                    $P3 = ((($Totalactivo + BalanceSemestral::proviciones($anio, $id)) - ($Totalactivosimproductivos +  $Totalpasivo -  $Depositosdesocios)) / ($Totalpatrimonio + $Depositosdesocios)) * 100;
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "P3";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "SOLVENCIA";
                    $Indicador->forindicador = "((activo total + provisiones) - (activos improductivos netos + pasivo total - Depositos de socios)) / (Patrimonio + Depositos de socios)";
                    $Indicador->calindicador = "(($Totalactivo + $Proviciones) - ($Totalactivosimproductivos +  $Totalpasivo -  $Depositosdesocios)) / ($Totalpatrimonio + $Depositosdesocios)";
                    $Indicador->valorindicador = round($P3, 2);
                    $Indicador->metindicador = ">=111%";
                    $Indicador->save();
                    $P4 = ($Totalpatrimonio / $Totalactivo) * 100;
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "P4";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "CAPACIDAD PATRIMONIAL";
                    $Indicador->forindicador = "Patrimonio / Total Activo";
                    $Indicador->calindicador = "$Totalpatrimonio / $Totalactivo";
                    $Indicador->valorindicador = round($P4, 2);
                    $Indicador->metindicador = "15%";
                    $Indicador->save();

                    $E1 = ($Carteraneta / $Totalactivo) * 100;
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "E1";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "PARTICIPACIÓN DE CARTERA NETA";
                    $Indicador->forindicador = "Cartera neta / Total Activo";
                    $Indicador->calindicador = "$Carteraneta / $Totalactivo";
                    $Indicador->valorindicador = round($E1, 2);
                    $Indicador->metindicador = "70% - 80%";
                    $Indicador->save();
                    $E2 = (BalanceSemestral::inversiones($anio, $id) / $Totalactivo) * 100;
                    $val = BalanceSemestral::inversiones($anio, $id);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "E2";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "PARTICIPACIÓN DE INVERSIONES NETAS";
                    $Indicador->forindicador = "Inversiones netas / Total Activo";
                    $Indicador->calindicador = "$val / $Totalactivo";
                    $Indicador->valorindicador = round($E2, 2);
                    $Indicador->metindicador = "<=16%";
                    $Indicador->save();
                    $E3 = ($Depositosdesocios / $Totalactivo) * 100;
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "E3";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "PARTICIPACIÓN DE AHORROS";
                    $Indicador->forindicador = "Total Depositos de socios / Total Activo";
                    $Indicador->calindicador = "$Depositosdesocios / $Totalactivo";
                    $Indicador->valorindicador = round($E3, 2);
                    $Indicador->metindicador = "70% - 80%";
                    $Indicador->save();
                    $E4 = ($Obligacionesfinancieras / $Totalactivo) * 100;
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "E4";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "PARTICIPACIÓN DE OBLIGACIONES FINANCIERAS";
                    $Indicador->forindicador = "Obligaciones Financieras / Total Activo";
                    $Indicador->calindicador = "$Obligacionesfinancieras / $Totalactivo";
                    $Indicador->valorindicador = round($E4, 2);
                    $Indicador->metindicador = "<=2%";
                    $Indicador->save();
                    $E5 = (BalanceSemestral::capitalsocial($anio, $id) / $Totalactivo) * 100;
                    $val = BalanceSemestral::capitalsocial($anio, $id);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "E5";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "PARTICIPACIÓN DE APORTACIONES DE SOCIOS";
                    $Indicador->forindicador = "Total Certificados de Aportacion / Total Activo";
                    $Indicador->calindicador = "$val / $Totalactivo";
                    $Indicador->valorindicador = round($E5, 2);
                    $Indicador->metindicador = "<=20%";
                    $Indicador->save();
                    $E6 = ($Reservastotales / $Totalactivo) * 100;
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "E6";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "PARTICIPACIÓN DE RESERVAS";
                    $Indicador->forindicador = "Total Reservas / Total Activo";
                    $Indicador->calindicador = "$Reservastotales / $Totalactivo";
                    $Indicador->valorindicador = round($E6, 2);
                    $Indicador->metindicador = ">=10%";
                    $Indicador->save();

                    $R1 = ((BalanceSemestral::interesesydescuentosdeinversionesentitulosvalores($anio, $id) + BalanceSemestral::interesesydescuentosdecarteradecreditos($anio, $id)) / ((BalanceSemestral::carteraporvencer($anio, $id) + BalanceSemestral::carteraporvencer($anio - 1, $id)) / 2)) * 100;
                    $val = (BalanceSemestral::interesesydescuentosdeinversionesentitulosvalores($anio, $id) + BalanceSemestral::interesesydescuentosdecarteradecreditos($anio, $id));
                    $val2 = (BalanceSemestral::carteraporvencer($anio, $id) + BalanceSemestral::carteraporvencer($anio - 1, $id)) / 2;
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "R1";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "RENDIMIENTO NETO DE PRÉSTAMOS";
                    $Indicador->forindicador = "Ingresos por cartera / Cartera Por Vencer Promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R1, 2);
                    $Indicador->metindicador = ">10%";
                    $Indicador->save();
                    $R2 = (BalanceSemestral::interesesydescuentosdeinversionesentitulosvalores($anio, $id) / ((BalanceSemestral::inversiones($anio, $id) + BalanceSemestral::inversiones($anio - 1, $id)) / 2)) * 100;
                    $val = BalanceSemestral::interesesydescuentosdeinversionesentitulosvalores($anio, $id);
                    $val2 = ((BalanceSemestral::inversiones($anio, $id) + BalanceSemestral::inversiones($anio - 1, $id)) / 2);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "R2";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "RENDIMIENTO NETO DE INVERSIONES";
                    $Indicador->forindicador = "Ingresos por inversiones / Inversiones Promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R2, 2);
                    $Indicador->metindicador = "Tasa del rendimiento";
                    $Indicador->save();
                    $R3 = (BalanceSemestral::interesesydescuentosganados($anio, $id) / (($Carteraporvencer + BalanceSemestral::inversiones($anio - 1, $id) + $Carteraporvencer + BalanceSemestral::inversiones($anio, $id)) / 2)) * 100;
                    $val = BalanceSemestral::interesesydescuentosganados($anio, $id);
                    $val2 = (($Carteraporvencer + BalanceSemestral::inversiones($anio - 1, $id) + $Carteraporvencer + BalanceSemestral::inversiones($anio, $id)) / 2);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "R3";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "RENDIMIENTO DE ACTIVOS PRODUCTIVOS";
                    $Indicador->forindicador = "ITotal Intereses ganados / Activo produtivo promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R3, 2);
                    $Indicador->metindicador = ">18%";
                    $Indicador->save();
                    $R4 = (BalanceSemestral::obligacionesconelpublico($anio, $id) / ((BalanceSemestral::obligacionesconelpublico($anio - 1, $id) + BalanceSemestral::obligacionesconelpublico($anio, $id)) / 2)) * 100;
                    $val = BalanceSemestral::obligacionesconelpublico($anio, $id);
                    $val2 = ((BalanceSemestral::obligacionesconelpublico($anio - 1, $id) + BalanceSemestral::obligacionesconelpublico($anio, $id)) / 2);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "R4";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "COSTO FINANCIERO AHORROS DE SOCIOS";
                    $Indicador->forindicador = "Intereses pagados por depositos / Depositos de Socios Promedio ";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R4, 2);
                    $Indicador->metindicador = "5%";
                    $Indicador->save();
                    $R5 = (BalanceSemestral::depositosdeahorro($anio, $id) / ((BalanceSemestral::depositosalavista($anio - 1, $id) + BalanceSemestral::depositosalavista($anio, $id)) / 2)) * 100;
                    $val = BalanceSemestral::depositosdeahorro($anio, $id);
                    $val2 = ((BalanceSemestral::depositosalavista($anio - 1, $id) + BalanceSemestral::depositosalavista($anio, $id)) / 2);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "R5";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "COSTO FINANCIERO AHORROS A LA VISTA";
                    $Indicador->forindicador = "Intereses pagados por ahorros a la vista / Ahorros a la vista Promedio ";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R5, 2);
                    $Indicador->metindicador = "<=2%";
                    $Indicador->save();
                    $R6 = (BalanceSemestral::depositosaplazo($anio, $id) / ((BalanceSemestral::depositosaplazo2($anio - 1, $id) + BalanceSemestral::depositosaplazo2($anio, $id)) / 2)) * 100;
                    $val = BalanceSemestral::depositosaplazo($anio, $id);
                    $val2 = ((BalanceSemestral::depositosaplazo2($anio - 1, $id) + BalanceSemestral::depositosaplazo2($anio, $id)) / 2);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "R6";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "COSTO FINANCIERO DEPÓSITOS A PLAZO";
                    $Indicador->forindicador = "Intereses pagados por depositos a plazo / Depositos a plazo Promedio  ";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R6, 2);
                    $Indicador->metindicador = ">=5%";
                    $Indicador->save();
                    $R7 = (BalanceSemestral::obligacionesfinancieras4103($anio, $id) / ((BalanceSemestral::obligacionesfinancieras($anio - 1, $id) + BalanceSemestral::obligacionesfinancieras($anio, $id)) / 2)) * 100;
                    $val = BalanceSemestral::obligacionesfinancieras4103($anio, $id);
                    $val2 = ((BalanceSemestral::obligacionesfinancieras($anio - 1, $id) + BalanceSemestral::obligacionesfinancieras($anio, $id)) / 2);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "R7";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "COSTO FINANCIERO OBLIGACIONES FINANCIERAS";
                    $Indicador->forindicador = "Intereses pagados por obligaciones financieras / Obligaciones financieras Promedio ";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R7, 2);
                    $Indicador->metindicador = ">=10%";
                    $Indicador->save();
                    $R8 = (BalanceSemestral::interesescausados($anio, $id) / (((BalanceSemestral::totalpasivo($anio - 1, $id) - $Pasivosconcosto) + (BalanceSemestral::totalpasivo($anio, $id) - $Pasivosconcosto)) / 2)) * 100;
                    $val = BalanceSemestral::interesescausados($anio, $id);
                    $val2 = (((BalanceSemestral::totalpasivo($anio - 1, $id) - $Pasivosconcosto) + (BalanceSemestral::totalpasivo($anio, $id) - $Pasivosconcosto)) / 2);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "R8";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "COSTO FINANCIERO TOTAL";
                    $Indicador->forindicador = "Total intereses pagados / Pasivos con costo promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R8, 2);
                    $Indicador->metindicador = "<=5%";
                    $Indicador->save();
                    $R9 = (BalanceSemestral::interesesydescuentosganados($anio, $id) / BalanceSemestral::interesescausados($anio, $id)) * 100;
                    $val = BalanceSemestral::interesesydescuentosganados($anio, $id);
                    $val2 = BalanceSemestral::interesescausados($anio, $id);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "R9";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "MARGEN BRUTO ";
                    $Indicador->forindicador = "Total intereses ganados / Total intereses pagados";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R9, 2);
                    $Indicador->metindicador = "300%";
                    $Indicador->save();
                    $R10 = (BalanceSemestral::gastosoperacion($anio, $id) / ((BalanceSemestral::interesesydescuentosganados($anio, $id) - BalanceSemestral::interesescausados($anio, $id)) + BalanceSemestral::ingresosporservicios($anio, $id) - $Proviciones)) * 100;
                    $val = BalanceSemestral::gastosoperacion($anio, $id);
                    $val2 = ((BalanceSemestral::interesesydescuentosganados($anio, $id) - BalanceSemestral::interesescausados($anio, $id)) + BalanceSemestral::ingresosporservicios($anio, $id) - $Proviciones);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "R10";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "GRADO DE ABSORCIÓN";
                    $Indicador->forindicador = "Gastos de operación / (Margen bruto financiero - Provisiones)";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R10, 2);
                    $Indicador->metindicador = "<100%";
                    $Indicador->save();
                    $R11 = (BalanceSemestral::gastosoperacion($anio, $id) / ((BalanceSemestral::totalactivo($anio - 1, $id) + BalanceSemestral::totalactivo($anio, $id)) / 2)) * 100;
                    $val = BalanceSemestral::gastosoperacion($anio, $id);
                    $val2 = ((BalanceSemestral::totalactivo($anio - 1, $id) + BalanceSemestral::totalactivo($anio, $id)) / 2);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "R11";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "TASA EFICIENCIA DEL ACTIVO";
                    $Indicador->forindicador = "Gastos de operación / Activo total promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R11, 2);
                    $Indicador->metindicador = "<5%";
                    $Indicador->save();
                    $R12 = (BalanceSemestral::gastosdepersonal($anio, $id) / ((BalanceSemestral::totalactivo($anio - 1, $id) + BalanceSemestral::totalactivo($anio, $id)) / 2)) * 100;
                    $val = BalanceSemestral::gastosdepersonal($anio, $id);
                    $val2 = ((BalanceSemestral::totalactivo($anio - 1, $id) + BalanceSemestral::totalactivo($anio, $id)) / 2);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "R12";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "TASA EFICIENCIA GASTOS DE PERSONAL";
                    $Indicador->forindicador = "Gastos de personal / Activo total promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R12, 2);
                    $Indicador->metindicador = "<3%";
                    $Indicador->save();
                    $R13 = ($Proviciones / (((BalanceSemestral::carteraporvencer($anio - 1, $id) + BalanceSemestral::carteraquenodevengaintereses($anio - 1, $id) + BalanceSemestral::carteravencida($anio - 1, $id) + BalanceSemestral::carteraquenodevengaintereses($anio - 1, $id) + BalanceSemestral::carteravencida($anio - 1, $id)) + (BalanceSemestral::carteraporvencer($anio, $id) + BalanceSemestral::carteraquenodevengaintereses($anio, $id) + BalanceSemestral::carteravencida($anio, $id) + BalanceSemestral::carteraquenodevengaintereses($anio, $id) + BalanceSemestral::carteravencida($anio, $id))) / 2)) * 100;
                    $val = $Proviciones;
                    $val2 = (((BalanceSemestral::carteraporvencer($anio - 1, $id) + BalanceSemestral::carteraquenodevengaintereses($anio - 1, $id) + BalanceSemestral::carteravencida($anio - 1, $id) + BalanceSemestral::carteraquenodevengaintereses($anio - 1, $id) + BalanceSemestral::carteravencida($anio - 1, $id)) + (BalanceSemestral::carteraporvencer($anio, $id) + BalanceSemestral::carteraquenodevengaintereses($anio, $id) + BalanceSemestral::carteravencida($anio, $id) + BalanceSemestral::carteraquenodevengaintereses($anio, $id) + BalanceSemestral::carteravencida($anio, $id))) / 2);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "R13";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "TASA DE PROVISIONES";
                    $Indicador->forindicador = "Gasto de provisiones / Cartera bruta promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R13, 2);
                    $Indicador->metindicador = "<2%";
                    $Indicador->save();
                    $R14 = (BalanceSemestral::perdidasyganancias($anio, $id) / ((BalanceSemestral::totalactivo($anio - 1, $id) + BalanceSemestral::totalactivo($anio, $id)) / 2)) * 100;
                    $val = BalanceSemestral::perdidasyganancias($anio, $id);
                    $val2 = ((BalanceSemestral::totalactivo($anio - 1, $id) + BalanceSemestral::totalactivo($anio, $id)) / 2);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "R14";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "RENDIMIENTO DEL ACTIVO TOTAL";
                    $Indicador->forindicador = "Resultado del ejercicio / Activo  promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R14, 2);
                    $Indicador->metindicador = "No inflación";
                    $Indicador->save();
                    $R15 = (BalanceSemestral::perdidasyganancias($anio, $id) / ((BalanceSemestral::totalpatrimonio($anio - 1, $id) + BalanceSemestral::totalpatrimonio($anio, $id)) / 2)) * 100;
                    $val = BalanceSemestral::perdidasyganancias($anio, $id);
                    $val2 = ((BalanceSemestral::totalpatrimonio($anio - 1, $id) + BalanceSemestral::totalpatrimonio($anio, $id)) / 2);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "R15";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "RENDIMIENTO DEL PATRIMONIO";
                    $Indicador->forindicador = "Resultado del ejercicio / Patrimonio promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R15, 2);
                    $Indicador->metindicador = ">10%";
                    $Indicador->save();
                    $R16 = (BalanceSemestral::perdidasyganancias($anio, $id) / ((BalanceSemestral::capitalsocial($anio - 1, $id) + BalanceSemestral::capitalsocial($anio, $id)) / 2)) * 100;
                    $val = BalanceSemestral::perdidasyganancias($anio, $id);
                    $val2 = ((BalanceSemestral::capitalsocial($anio - 1, $id) + BalanceSemestral::capitalsocial($anio, $id)) / 2);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "R16";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "RENDIMIENTO DE CERTIFICADOS DE APORTACIÓN";
                    $Indicador->forindicador = "Resultado del ejercicio / Certificados de aportacion  promedio";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($R16, 2);
                    $Indicador->metindicador = ">12%";
                    $Indicador->save();

                    $L1 = (BalanceSemestral::fondosdisponibles($anio, $id) / $Totaldepositoscortoplazo) * 100;
                    $val = BalanceSemestral::fondosdisponibles($anio, $id);
                    $val2 = $Totaldepositoscortoplazo;
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "L1";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "LIQUIDEZ CORRIENTE";
                    $Indicador->forindicador = "Fondos disponibles / Depositos de socios a corto plazo";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($L1, 2);
                    $Indicador->metindicador = "15% - 20%";
                    $Indicador->save();
                    $L2 = ((BalanceSemestral::fondosdisponibles($anio, $id) + BalanceSemestral::inversiones($anio, $id)) / BalanceSemestral::depositosdesocios($anio, $id)) * 100;
                    $val = BalanceSemestral::fondosdisponibles($anio, $id);
                    $val11 = BalanceSemestral::inversiones($anio, $id);
                    $val2 = BalanceSemestral::depositosdesocios($anio, $id);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "L2";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "LIQUIDEZ GENERAL";
                    $Indicador->forindicador = "Fondos disponibles + Inversiones / Depositos de socios";
                    $Indicador->calindicador = "($val + $val11) / $val2";
                    $Indicador->valorindicador = round($L2, 2);
                    $Indicador->metindicador = "20%";
                    $Indicador->save();

                    $A1 = ($Carteravencida / $Carterabruta) * 100;
                    $val = $Carteravencida;
                    $val2 = $Carterabruta;
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "A1";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "INDICADOR DE CARTERA VENCIDA";
                    $Indicador->forindicador = "Total Cartera Vencida / Total Cartera Bruta";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($A1, 2);
                    $Indicador->metindicador = "<=3%";
                    $Indicador->save();
                    $A2 = ($Carteraimproductiva / $Carterabruta) * 100;
                    $val = $Carteraimproductiva;
                    $val2 = $Carterabruta;
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "A2";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "INDICADOR DE MOROSIDAD AMPLIADA";
                    $Indicador->forindicador = "Total Cartera Improductiva / Total Cartera Bruta";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($A2, 2);
                    $Indicador->metindicador = "<=5%";
                    $Indicador->save();
                    $A3 = ($Totalactivosimproductivos / $Totalactivo) * 100;
                    $val = $Totalactivosimproductivos;
                    $val2 = $Totalactivo;
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "A3";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "INDICADOR DE ACTIVOS IMPRODUCTIVOS";
                    $Indicador->forindicador = "Activos Improductivos / Total Activo";
                    $Indicador->calindicador = "$val / $val2";
                    $Indicador->valorindicador = round($A3, 2);
                    $Indicador->metindicador = "<=5%";
                    $Indicador->save();
                    $A4 = (($Totalpatrimonio + $Pasivossincosto) / $Totalactivosimproductivos) * 100;
                    $val = $Totalpatrimonio;
                    $val1 = $Pasivossincosto;
                    $val2 = $Totalactivosimproductivos;
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "A4";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "PROTECCIÓN DE ACTIVOS IMPRODUCTIVOS";
                    $Indicador->forindicador = "(Patrimonio + Pasivos sin costo) / Activos Improductivos";
                    $Indicador->calindicador = "($val + $val1) / $val2";
                    $Indicador->valorindicador = round($A4, 2);
                    $Indicador->metindicador = ">=200%";
                    $Indicador->save();

                    $S1 = (BalanceSemestral::fondosdisponibles($anio, $id) / BalanceSemestral::fondosdisponibles($anio - 1, $id) - 1) * 100;
                    $val = BalanceSemestral::fondosdisponibles($anio, $id);
                    $val2 = BalanceSemestral::fondosdisponibles($anio - 1, $id);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "S1";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE FONDOS DISPONIBLES";
                    $Indicador->forindicador = "(Saldo actual de fondos disponibles / Saldo fondos disponibles periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S1, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S2 = ($Carterabruta / (BalanceSemestral::carteraporvencer($anio, $id) + BalanceSemestral::carteraquenodevengaintereses($anio, $id) + BalanceSemestral::carteravencida($anio, $id) + BalanceSemestral::proviciones($anio - 1, $id)) - 1) * 100;
                    $val = $Carterabruta;
                    $val2 = (BalanceSemestral::carteraporvencer($anio, $id) + BalanceSemestral::carteraquenodevengaintereses($anio, $id) + BalanceSemestral::carteravencida($anio, $id) + BalanceSemestral::proviciones($anio - 1, $id));
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "S2";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE PRESTAMOS";
                    $Indicador->forindicador = "(Saldo actual de cartera / Saldo cartera periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S2, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S3 = (BalanceSemestral::obligacionesconelpublico($anio, $id) / BalanceSemestral::obligacionesconelpublico($anio - 1, $id) - 1) * 100;
                    $val = BalanceSemestral::obligacionesconelpublico($anio, $id);
                    $val2 = BalanceSemestral::obligacionesconelpublico($anio - 1, $id);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "S3";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE DEPOSITOS DE SOCIOS";
                    $Indicador->forindicador = "(Saldo actual de depositos de socios / Saldo depositos de socios periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S3, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S4 = (BalanceSemestral::depositosalavista($anio, $id) / BalanceSemestral::depositosalavista($anio - 1, $id) - 1) * 100;
                    $val = BalanceSemestral::depositosalavista($anio, $id);
                    $val2 = BalanceSemestral::depositosalavista($anio - 1, $id);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "S4";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE AHORROS A LA VISTA";
                    $Indicador->forindicador = "(Saldo actual de ahorros a la vista / Saldo ahorros a la vista periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S4, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S5 = (BalanceSemestral::depositosaplazo2($anio, $id) / BalanceSemestral::depositosaplazo2($anio - 1, $id) - 1) * 100;
                    $val = BalanceSemestral::depositosaplazo2($anio, $id);
                    $val2 = BalanceSemestral::depositosaplazo2($anio - 1, $id);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "S5";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE DEPOSITOS A PLAZO";
                    $Indicador->forindicador = "(Saldo actual de depositos a plazo / Saldo depositos a plazo periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S5, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S6 = (BalanceSemestral::obligacionesfinancieras($anio, $id) / BalanceSemestral::obligacionesfinancieras($anio - 1, $id) - 1) * 100;
                    $val = BalanceSemestral::obligacionesfinancieras($anio, $id);
                    $val2 = BalanceSemestral::obligacionesfinancieras($anio - 1, $id);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "S6";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE OBLIGACIONES FINANCIERAS";
                    $Indicador->forindicador = "(Saldo actual de obligaciones financieras / Saldo obligaciones financieras periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S6, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S7 = (BalanceSemestral::capitalsocial($anio, $id) / BalanceSemestral::capitalsocial($anio - 1, $id) - 1) * 100;
                    $val = BalanceSemestral::capitalsocial($anio, $id);
                    $val2 = BalanceSemestral::capitalsocial($anio - 1, $id);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "S7";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DE CERTIFICADOS DE APORTACION";
                    $Indicador->forindicador = "(Saldo actual de certificados de aportacion / Saldo certificados de aportacion periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S7, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S8 = (BalanceSemestral::totalpatrimonio($anio, $id) / BalanceSemestral::totalpatrimonio($anio - 1, $id) - 1) * 100;
                    $val = BalanceSemestral::totalpatrimonio($anio, $id);
                    $val2 = BalanceSemestral::totalpatrimonio($anio - 1, $id);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "S8";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DEL PATRIMONIO";
                    $Indicador->forindicador = "(Saldo actual de patrimonio / Saldo patrimonio periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S8, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S9 = (BalanceSemestral::totalactivo($anio, $id) / BalanceSemestral::totalactivo($anio - 1, $id) - 1) * 100;
                    $val = BalanceSemestral::totalactivo($anio, $id);
                    $val2 = BalanceSemestral::totalactivo($anio - 1, $id);
                    $Indicador = new IndicadoresSemestral;
                    $Indicador->codindicador = "S9";
                    $Indicador->id = $id;
                    $Indicador->codaniosemestral = $anio;
                    $Indicador->nomindicador = "CRECIMIENTO DEL NÚMERO DE SOCIOS";
                    $Indicador->forindicador = "(Total número de socios actual / Saldo número de socios periodo anterior)-1";
                    $Indicador->calindicador = "($val / $val2)-1";
                    $Indicador->valorindicador = round($S9, 2);
                    $Indicador->metindicador = "Ninguna";
                    $Indicador->save();
                    $S10 = 0;
                } else {

                    $Carteraporvencer = BalanceSemestral::carteraporvencer($anio, $id);
                    $Carteraquenodevengaintereses = BalanceSemestral::carteraquenodevengaintereses($anio, $id);
                    $Carteravencida = BalanceSemestral::carteravencida($anio, $id);
                    $Carteraimproductiva = $Carteraquenodevengaintereses + $Carteravencida;
                    $Carterabruta = $Carteraporvencer + $Carteraimproductiva;
                    $Proviciones =  BalanceSemestral::proviciones($anio, $id);
                    $Carteraneta = $Carterabruta + $Proviciones;

                    $Cuentasporcobrar = BalanceSemestral::cuentasporcobrar($anio, $id);
                    $Bienesendaciondepago = BalanceSemestral::bienesendaciondepago($anio, $id);
                    $Activosfijos = BalanceSemestral::activosfijos($anio, $id);
                    $Otrosactivos = BalanceSemestral::otrosactivos($anio, $id);
                    $Totalactivosimproductivos = $Cuentasporcobrar + $Bienesendaciondepago + $Activosfijos + $Otrosactivos + $Carteraimproductiva;

                    $Depositosdesocios = BalanceSemestral::depositosdesocios($anio, $id);
                    $Obligacionesfinancieras = BalanceSemestral::obligacionesfinancieras($anio, $id);
                    $Pasivosconcosto = $Depositosdesocios + $Obligacionesfinancieras;

                    $Reservastotales = BalanceSemestral::reservastotales($anio, $id);

                    $Depositosalavista = BalanceSemestral::depositosalavista($anio, $id);
                    $de1a30dias = BalanceSemestral::de1a30dias($anio, $id);
                    $de31a90dias = BalanceSemestral::de31a90dias($anio, $id);
                    $oblde1a30dias = BalanceSemestral::oblde1a30dias($anio, $id);
                    $oblde31a90dias = BalanceSemestral::oblde31a90dias($anio, $id);

                    $Totaldepositoscortoplazo = $Depositosalavista + $de1a30dias + $de31a90dias + $oblde1a30dias + $oblde31a90dias;

                    $Activoproductivo = $Carteraporvencer + BalanceSemestral::inversiones($anio, $id);
                    $Pasivossincosto =  BalanceSemestral::totalpasivo($anio, $id) - $Pasivosconcosto;

                    $Totalactivo = BalanceSemestral::totalactivo($anio, $id);
                    $Totalpasivo = BalanceSemestral::totalpasivo($anio, $id);
                    $Totalpatrimonio = BalanceSemestral::totalpatrimonio($anio, $id);

                    $P1 = - ($Proviciones / $Carteravencida) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("P1", $anio, $id);
                    $Indicador->valorindicador = round($P1, 2);
                    $Indicador->save();
                    $P2 = - ($Proviciones / $Carteraimproductiva) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("P2", $anio, $id);
                    $Indicador->valorindicador = round($P2, 2);
                    $Indicador->save();
                    $P3 = ((($Totalactivo + BalanceSemestral::proviciones($anio, $id)) - ($Totalactivosimproductivos +  $Totalpasivo -  $Depositosdesocios)) / ($Totalpatrimonio + $Depositosdesocios)) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("P3", $anio, $id);
                    $Indicador->valorindicador = round($P3, 2);
                    $Indicador->save();
                    $P4 = ($Totalpatrimonio / $Totalactivo) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("P4", $anio, $id);
                    $Indicador->valorindicador = round($P4, 2);
                    $Indicador->save();

                    $E1 = ($Carteraneta / $Totalactivo) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("E1", $anio, $id);
                    $Indicador->valorindicador = round($E1, 2);
                    $Indicador->save();
                    $E2 = (BalanceSemestral::inversiones($anio, $id) / $Totalactivo) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("E2", $anio, $id);
                    $Indicador->valorindicador = round($E2, 2);
                    $Indicador->save();
                    $E3 = ($Depositosdesocios / $Totalactivo) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("E3", $anio, $id);
                    $Indicador->valorindicador = round($E3, 2);
                    $Indicador->save();
                    $E4 = ($Obligacionesfinancieras / $Totalactivo) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("E4", $anio, $id);
                    $Indicador->valorindicador = round($E4, 2);
                    $Indicador->save();
                    $E5 = (BalanceSemestral::capitalsocial($anio, $id) / $Totalactivo) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("E5", $anio, $id);
                    $Indicador->valorindicador = round($E5, 2);
                    $Indicador->save();
                    $E6 = ($Reservastotales / $Totalactivo) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("E6", $anio, $id);
                    $Indicador->valorindicador = round($E6, 2);
                    $Indicador->save();

                    $R1 = ((BalanceSemestral::interesesydescuentosdeinversionesentitulosvalores($anio, $id) + BalanceSemestral::interesesydescuentosdecarteradecreditos($anio, $id)) / ((BalanceSemestral::carteraporvencer($anio, $id) + BalanceSemestral::carteraporvencer($anio - 1, $id)) / 2)) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("R1", $anio, $id);
                    $Indicador->valorindicador = round($R1, 2);
                    $Indicador->save();
                    $R2 = (BalanceSemestral::interesesydescuentosdeinversionesentitulosvalores($anio, $id) / ((BalanceSemestral::inversiones($anio, $id) + BalanceSemestral::inversiones($anio - 1, $id)) / 2)) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("R2", $anio, $id);
                    $Indicador->valorindicador = round($R2, 2);
                    $Indicador->save();
                    $R3 = (BalanceSemestral::interesesydescuentosganados($anio, $id) / (($Carteraporvencer + BalanceSemestral::inversiones($anio - 1, $id) + $Carteraporvencer + BalanceSemestral::inversiones($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("R3", $anio, $id);
                    $Indicador->valorindicador = round($R3, 2);
                    $Indicador->save();
                    $R4 = (BalanceSemestral::obligacionesconelpublico($anio, $id) / ((BalanceSemestral::obligacionesconelpublico($anio - 1, $id) + BalanceSemestral::obligacionesconelpublico($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("R4", $anio, $id);
                    $Indicador->valorindicador = round($R4, 2);
                    $Indicador->save();
                    $R5 = (BalanceSemestral::depositosdeahorro($anio, $id) / ((BalanceSemestral::depositosalavista($anio - 1, $id) + BalanceSemestral::depositosalavista($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("R5", $anio, $id);
                    $Indicador->valorindicador = round($R5, 2);
                    $Indicador->save();
                    $R6 = (BalanceSemestral::depositosaplazo($anio, $id) / ((BalanceSemestral::depositosaplazo2($anio - 1, $id) + BalanceSemestral::depositosaplazo2($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("R6", $anio, $id);
                    $Indicador->valorindicador = round($R6, 2);
                    $Indicador->save();
                    $R7 = (BalanceSemestral::obligacionesfinancieras4103($anio, $id) / ((BalanceSemestral::obligacionesfinancieras($anio - 1, $id) + BalanceSemestral::obligacionesfinancieras($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("R7", $anio, $id);
                    $Indicador->valorindicador = round($R7, 2);
                    $Indicador->save();
                    $R8 = (BalanceSemestral::interesescausados($anio, $id) / (((BalanceSemestral::totalpasivo($anio - 1, $id) - $Pasivosconcosto) + (BalanceSemestral::totalpasivo($anio, $id) - $Pasivosconcosto)) / 2)) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("R8", $anio, $id);
                    $Indicador->valorindicador = round($R8, 2);
                    $Indicador->save();
                    $R9 = (BalanceSemestral::interesesydescuentosganados($anio, $id) / BalanceSemestral::interesescausados($anio, $id)) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("R9", $anio, $id);
                    $Indicador->valorindicador = round($R9, 2);
                    $Indicador->save();
                    $R10 = (BalanceSemestral::gastosoperacion($anio, $id) / ((BalanceSemestral::interesesydescuentosganados($anio, $id) - BalanceSemestral::interesescausados($anio, $id)) + BalanceSemestral::ingresosporservicios($anio, $id) - $Proviciones)) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("R10", $anio, $id);
                    $Indicador->valorindicador = round($R10, 2);
                    $Indicador->save();
                    $R11 = (BalanceSemestral::gastosoperacion($anio, $id) / ((BalanceSemestral::totalactivo($anio - 1, $id) + BalanceSemestral::totalactivo($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("R11", $anio, $id);
                    $Indicador->valorindicador = round($R11, 2);
                    $Indicador->save();
                    $R12 = (BalanceSemestral::gastosdepersonal($anio, $id) / ((BalanceSemestral::totalactivo($anio - 1, $id) + BalanceSemestral::totalactivo($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("R12", $anio, $id);
                    $Indicador->valorindicador = round($R12, 2);
                    $Indicador->save();
                    $R13 = ($Proviciones / (((BalanceSemestral::carteraporvencer($anio - 1, $id) + BalanceSemestral::carteraquenodevengaintereses($anio - 1, $id) + BalanceSemestral::carteravencida($anio - 1, $id) + BalanceSemestral::carteraquenodevengaintereses($anio - 1, $id) + BalanceSemestral::carteravencida($anio - 1, $id)) + (BalanceSemestral::carteraporvencer($anio, $id) + BalanceSemestral::carteraquenodevengaintereses($anio, $id) + BalanceSemestral::carteravencida($anio, $id) + BalanceSemestral::carteraquenodevengaintereses($anio, $id) + BalanceSemestral::carteravencida($anio, $id))) / 2)) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("R13", $anio, $id);
                    $Indicador->valorindicador = round($R13, 2);
                    $Indicador->save();
                    $R14 = (BalanceSemestral::perdidasyganancias($anio, $id) / ((BalanceSemestral::totalactivo($anio - 1, $id) + BalanceSemestral::totalactivo($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("R14", $anio, $id);
                    $Indicador->valorindicador = round($R14, 2);
                    $Indicador->save();
                    $R15 = (BalanceSemestral::perdidasyganancias($anio, $id) / ((BalanceSemestral::totalpatrimonio($anio - 1, $id) + BalanceSemestral::totalpatrimonio($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("R15", $anio, $id);
                    $Indicador->valorindicador = round($R15, 2);
                    $Indicador->save();
                    $R16 = (BalanceSemestral::perdidasyganancias($anio, $id) / ((BalanceSemestral::capitalsocial($anio - 1, $id) + BalanceSemestral::capitalsocial($anio, $id)) / 2)) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("R16", $anio, $id);
                    $Indicador->valorindicador = round($R16, 2);
                    $Indicador->save();

                    $L1 = (BalanceSemestral::fondosdisponibles($anio, $id) / $Totaldepositoscortoplazo) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("L1", $anio, $id);
                    $Indicador->valorindicador = round($L1, 2);
                    $Indicador->save();
                    $L2 = ((BalanceSemestral::fondosdisponibles($anio, $id) + BalanceSemestral::inversiones($anio, $id)) / BalanceSemestral::depositosdesocios($anio, $id)) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("L2", $anio, $id);
                    $Indicador->valorindicador = round($L2, 2);
                    $Indicador->save();

                    $A1 = ($Carteravencida / $Carterabruta) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("A1", $anio, $id);
                    $Indicador->valorindicador = round($A1, 2);
                    $Indicador->save();
                    $A2 = ($Carteraimproductiva / $Carterabruta) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("A2", $anio, $id);
                    $Indicador->valorindicador = round($A2, 2);
                    $Indicador->save();
                    $A3 = ($Totalactivosimproductivos / $Totalactivo) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("A3", $anio, $id);
                    $Indicador->valorindicador = round($A3, 2);
                    $Indicador->save();
                    $A4 = (($Totalpatrimonio + $Pasivossincosto) / $Totalactivosimproductivos) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("A4", $anio, $id);
                    $Indicador->valorindicador = round($A4, 2);
                    $Indicador->save();

                    $S1 = (BalanceSemestral::fondosdisponibles($anio, $id) / BalanceSemestral::fondosdisponibles($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("S1", $anio, $id);
                    $Indicador->valorindicador = round($S1, 2);
                    $Indicador->save();
                    $S2 = ($Carterabruta / (BalanceSemestral::carteraporvencer($anio, $id) + BalanceSemestral::carteraquenodevengaintereses($anio, $id) + BalanceSemestral::carteravencida($anio, $id) + BalanceSemestral::proviciones($anio - 1, $id)) - 1) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("S2", $anio, $id);
                    $Indicador->valorindicador = round($S2, 2);
                    $Indicador->save();
                    $S3 = (BalanceSemestral::obligacionesconelpublico($anio, $id) / BalanceSemestral::obligacionesconelpublico($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("S3", $anio, $id);
                    $Indicador->valorindicador = round($S3, 2);
                    $Indicador->save();
                    $S4 = (BalanceSemestral::depositosalavista($anio, $id) / BalanceSemestral::depositosalavista($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("S4", $anio, $id);
                    $Indicador->valorindicador = round($S4, 2);
                    $Indicador->save();
                    $S5 = (BalanceSemestral::depositosaplazo2($anio, $id) / BalanceSemestral::depositosaplazo2($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("S5", $anio, $id);
                    $Indicador->valorindicador = round($S5, 2);
                    $Indicador->save();
                    $S6 = (BalanceSemestral::obligacionesfinancieras($anio, $id) / BalanceSemestral::obligacionesfinancieras($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("S6", $anio, $id);
                    $Indicador->valorindicador = round($S6, 2);
                    $Indicador->save();
                    $S7 = (BalanceSemestral::capitalsocial($anio, $id) / BalanceSemestral::capitalsocial($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("S7", $anio, $id);
                    $Indicador->valorindicador = round($S7, 2);
                    $Indicador->save();
                    $S8 = (BalanceSemestral::totalpatrimonio($anio, $id) / BalanceSemestral::totalpatrimonio($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("S8", $anio, $id);
                    $Indicador->valorindicador = round($S8, 2);
                    $Indicador->save();
                    $S9 = (BalanceSemestral::totalactivo($anio, $id) / BalanceSemestral::totalactivo($anio - 1, $id) - 1) * 100;
                    $Indicador = IndicadoresSemestral::Indicador("S9", $anio, $id);
                    $Indicador->valorindicador = round($S9, 2);
                    $Indicador->save();
                    $S10 = 0;
                }
            }

            $Anios = BalanceSemestral::listaranios($id);
            return view('administrador/administradorIndicadoresSemestral', compact('Anios'));
        } else {
            return view('auth/login');
        }
    }

    public function indicadoress(Request $request, $codaniosemestre)
    {
        $id = Auth::id();
        $Resumen = [];

        if ($request->ajax()) {
            $Indicadores = IndicadoresSemestral::Indicadores($codaniosemestre, $id);
            foreach ($Indicadores as $indi) {
                $Indicadoresanioanterior = IndicadoresSemestral::Indicador($indi['codindicador'], $codaniosemestre - 1, $id);
                $Resumen = array(
                    "codindicador" => $indi['codindicador'],
                    "nomindicador" => $indi['nomindicador'],
                    "forindicador" => $indi['forindicador'],
                    "calindicador" => $indi['calindicador'],
                    "metindicador" => $indi['metindicador'],
                    "nomanio" => $indi['nomanio'],
                    "nomsemestre" => $indi['nomsemestre'],
                    "valorindicador" => $indi['valorindicador'],
                    "nomanio2" => $Indicadoresanioanterior->nomanio,
                    "nomsemestre2" => $Indicadoresanioanterior->nomsemestre,
                    "forindicador2" => $Indicadoresanioanterior->forindicador,
                    "calindicador2" => $Indicadoresanioanterior->calindicador,
                    "valorindicador2" => $Indicadoresanioanterior->valorindicador,
                );
                $Resultado[] = $Resumen;
            }
            return response()->json($Resultado);
        }
    }
}
