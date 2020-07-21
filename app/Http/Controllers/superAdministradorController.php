<?php

namespace App\Http\Controllers;

use App\Anio;
use App\AnioMes;
use App\AnioSemestre;
use App\Balance;
use App\Imports\BalanceExport;
use App\IndicadoresAnual;
use App\Mes;
use App\Semestre;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class superAdministradorController extends Controller
{
    public function superAdministradorPrincipal(Request $request)
    {
        $User = User::User($request->id);
        \Cache::put('usercuenta', $request->id, 500);
        return view('superadministrador/superadministradorPrincipal', compact('User'));
    }

    public function InformacionAdministrador()
    {
        $id = Auth::id();
        $informacionPersonal = User::where('users.id', $id)->get();
        if (!is_null($informacionPersonal)) {
            return view('superadministrador/superadministradorInformacion', compact('informacionPersonal'));
        } else {
            return response('Usuario no encontrado', 404);
        }
    }

    public function UpdateAdministrador(Request $request)
    {
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
        return redirect()->route('InformacionAdministrador');
    }

    public function subalanceAnual(Request $request)
    {
        $Anios = Anio::all();
        $User = User::User(\Cache::get('usercuenta'));
        return view('superadministrador/superadministradorBalanceAnual', compact('Anios', 'User'));
    }

    public function suexcel(Request $request)
    {
        $balance = $request->file('balance');
        $codanio = $request->anio;
        $id = \Cache::get('usercuenta');
        Excel::import(new BalanceExport($codanio, $id), $balance);
        return back();
    }

    public function sugetconosinbalance(Request $request, $codanio)
    {
        $id = \Cache::get('usercuenta');
        if ($request->ajax()) {
            $BalanceAnual = Balance::BalanceAnual($codanio, $id);
            return response()->json($BalanceAnual);
        }
    }

    public function sueliminarbalance(Request $request)
    {
        $id = \Cache::get('usercuenta');
        $codanio = $request->anio;
        Balance::eliminarbalanceanual($codanio, $id);
        return response()->json(['success' => 'El balance se eliminó']);
    }

    public function suactualizarbalance(Request $request)
    {
        $id = \Cache::get('usercuenta');
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

    public function suResumenBalanceAnual()
    {
        $id = \Cache::get('usercuenta');
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
        $User = User::User(\Cache::get('usercuenta'));
        return view('superadministrador/superadministradorResumenBalanceAnual', compact('Resultado', 'User'));
    }

    public function suIndicadoresAnual()
    {
        $id = \Cache::get('usercuenta');
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
        $User = User::User(\Cache::get('usercuenta'));
        return view('superadministrador/superadministradorIndicadoresAnual', compact('Anios', 'User'));
    }

    public function suindicadores(Request $request, $codanio)
    {
        $id = \Cache::get('usercuenta');
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


    //////////////////////////////////////////////////////


    public function superAdministradoresPrincipal()
    {
        return view('superadministrador/superadministradoresPrincipal');
    }

    public function susbalanceAnual(Request $request)
    {
        $Anios = Anio::all();
        $id = Auth::id();
        return view('superadministrador/superadministradoresBalanceAnual', compact('Anios'));
    }

    public function susexcel(Request $request)
    {
        $balance = $request->file('balance');
        $id = Auth::id();
        $codanio = $request->anio;
        Excel::import(new BalanceExport($codanio, $id), $balance);
        return back();
    }

    public function susgetconosinbalance(Request $request, $codanio)
    {
        $id = Auth::id();
        if ($request->ajax()) {
            $BalanceAnual = Balance::BalanceAnual($codanio, $id);
            return response()->json($BalanceAnual);
        }
    }

    public function suseliminarbalance(Request $request)
    {
        $id = Auth::id();
        $codanio = $request->anio;
        Balance::eliminarbalanceanual($codanio, $id);
        return response()->json(['success' => 'El balance se eliminó']);
    }

    public function susactualizarbalance(Request $request)
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

    public function susResumenBalanceAnual()
    {
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
        return view('superadministrador/superadministradoresResumenBalanceAnual', compact('Resultado'));
    }

    public function susIndicadoresAnual()
    {
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
        return view('superadministrador/superadministradoresIndicadoresAnual', compact('Anios'));
    }

    public function susindicadores(Request $request, $codanio)
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

    public function superAdministradoresRegistrar()
    {
        $Users = User::all();
        return view('superadministrador/superadministradoresRegistrar', compact('Users'));
    }

    public function addUsuario(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name' => 'required|string|unique:users',
            'email'    => 'required|email|unique:users',
            'telefono' => 'required|string|max:10',
        ]);

        if ($v->fails()) {
            return redirect()->back()->withInput()->withErrors($v->errors());
        } else {
            $now = new \DateTime();
            $user = new User;
            $user->name = $request->name;
            $user->nombre = mb_strtoupper($request->nombre);
            $user->telefono = $request->telefono;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->remember_token = str_random(50);
            $user->created_at = $now;
            $user->updated_at = $now;
            $user->tipuser = $request->tipuser;
            $user->foto = "default.png";
            $user->save();
            return redirect()->route('superAdministradoresRegistrar');
        }
    }

    public function editUsuario(request $request)
    {
        if ($request->ajax()) {

            $post = User::find($request->id);
            $post->nombre = mb_strtoupper($request->nombre);
            $post->telefono = $request->telefono;
            $post->email = $request->email;
            $post->tipuser = $request->tipuser;
            $post->save();
            return response()->json($post);
        }
    }

    public function superAdministradoresAno()
    {
        $Anios = Anio::all();
        return view('superadministrador/superadministradoresAno', compact('Anios'));
    }

    public function addAno(Request $request)
    {
        $user = new Anio;
        $user->nomanio = $request->nombre;
        $user->save();

        $anios = Anio::all();
        $anio = $anios->last();
        $mes = Mes::all();
        foreach ($mes as $me) {
            $user = new AnioMes();
            $user->codanio = $anio->codanio;
            $user->codmes = $me['codmes'];
            $user->save();
        }

        $semestre = Semestre::all();
        foreach ($semestre as $se) {
            $user = new AnioSemestre();
            $user->codanio = $anio->codanio;
            $user->codsemestre = $se['codsemestre'];
            $user->save();
        }

        return redirect()->route('superAdministradoresAno');
    }

    public function deleteAno(request $request)
    {
        $aniomes = AnioMes::where('codanio', '=', $request->codanio)->get();
        if (empty($aniomes->first()->codaniomes)) {
        } else {
            foreach ($aniomes as $dato) {
                $ids[] = $dato->codaniomes;
            }
            AnioMes::destroy($ids);
        }

        $post = Anio::find($request->codanio)->delete();
        return response()->json(['success' => 'El año se eliminó, actualice la página']);
    }
}
