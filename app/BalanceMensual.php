<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BalanceMensual extends Model
{
    public $timestamps = false;
    protected $table = 'balancemensual';
    protected $primaryKey = 'codbalancemensual';

    protected $fillable = ['codbalancemensual', 'id', 'codaniomes', 'codcontable', 'nomcuenta', 'valorbalance'];

    public static function BalanceMensual($codaniomes, $id)
    {
        return BalanceMensual::join('aniomes', 'aniomes.codaniomes', '=', 'balancemensual.codaniomes')
            ->where('balancemensual.codaniomes', '=', $codaniomes)
            ->where('balancemensual.id', '=', $id)->get();
    }

    public static function eliminarbalancemensual($codaniomes, $id)
    {
        BalanceMensual::where('balancemensual.codaniomes', '=', $codaniomes)
            ->where('balancemensual.id', '=', $id)->delete();
    }

    public static function balance($codaniomes, $id, $codcontable)
    {
        return BalanceMensual::where('balancemensual.codaniomes', '=', $codaniomes)
            ->where('balancemensual.id', '=', $id)
            ->where('balancemensual.codcontable', '=', $codcontable)->first();
    }

    public static function listaranios($id)
    {
        return BalanceMensual::distinct()->select('balancemensual.codaniomes', 'anio.nomanio', 'mes.nommes')
            ->join('aniomes', 'aniomes.codaniomes', '=', 'balancemensual.codaniomes')
            ->join('anio', 'anio.codanio', '=', 'aniomes.codanio')
            ->join('mes', 'mes.codmes', '=', 'aniomes.codmes')
            ->where('balancemensual.id', '=', $id)->get();
    }

    public static function carteraporvencer($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '1402')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        $val2 = BalanceMensual::where('balancemensual.codcontable', '=', '1403')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        $val3 = BalanceMensual::where('balancemensual.codcontable', '=', '1404')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1) && empty($val2) && empty($val3)) {
            return 0;
        } else {
            $valor = $val1->valorbalance + $val2->valorbalance + $val3->valorbalance;
            return $valor;
        }
    }

    public static function carteraquenodevengaintereses($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '1426')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        $val2 = BalanceMensual::where('balancemensual.codcontable', '=', '1427')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        $val3 = BalanceMensual::where('balancemensual.codcontable', '=', '1428')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1) && empty($val2) && empty($val3)) {
            return 0;
        } else {
            $valor = $val1->valorbalance + $val2->valorbalance + $val3->valorbalance;
            return $valor;
        }
    }

    public static function carteravencida($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '1450')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        $val2 = BalanceMensual::where('balancemensual.codcontable', '=', '1451')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        $val3 = BalanceMensual::where('balancemensual.codcontable', '=', '1452')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1) && empty($val2) && empty($val3)) {
            return 0;
        } else {
            $valor = $val1->valorbalance + $val2->valorbalance + $val3->valorbalance;
            return $valor;
        }
    }

    public static function proviciones($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '1499')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function cuentasporcobrar($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '16')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function bienesendaciondepago($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '17')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function activosfijos($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '19')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function otrosactivos($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '19')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function depositosdesocios($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '21')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function obligacionesfinancieras($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '26')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function reservastotales($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '3')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        $val2 = BalanceMensual::where('balancemensual.codcontable', '=', '31')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();

        if (empty($val1 && empty($val2))) {
            return 0;
        } else {
            $valor = $val1->valorbalance - $val2->valorbalance;
            return $valor;
        }
    }

    public static function depositosalavista($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '2101')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function de1a30dias($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '210305')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function de31a90dias($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '210310')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function oblde1a30dias($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '260405')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        $val2 = BalanceMensual::where('balancemensual.codcontable', '=', '260605')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();

        if (empty($val1 && empty($val2))) {
            return 0;
        } else {
            $valor = $val1->valorbalance + $val2->valorbalance;
            return $valor;
        }
    }

    public static function oblde31a90dias($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '260410')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        $val2 = BalanceMensual::where('balancemensual.codcontable', '=', '260610')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();

        if (empty($val1 && empty($val2))) {
            return 0;
        } else {
            $valor = $val1->valorbalance - $val2->valorbalance;
            return $valor;
        }
    }

    public static function inversiones($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '13')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function totalpasivo($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '2')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function totalactivo($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '1')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function totalpatrimonio($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '3')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function capitalsocial($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '31')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function interesesydescuentosdeinversionesentitulosvalores($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '5103')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function interesesydescuentosdecarteradecreditos($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '5104')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function interesesydescuentosganados($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '51')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function obligacionesconelpublico($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '21')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function depositosdeahorro($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '410115')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function depositosaplazo($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '410130')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function depositosaplazo2($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '2103')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function obligacionesfinancieras4103($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '4103')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function interesescausados($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '41')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function gastosoperacion($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '45')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function ingresosporservicios($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '54')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function gastosdepersonal($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '4501')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function perdidasyganancias($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '59')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function fondosdisponibles($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '11')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function utilidadoexecedentedelejericicio($anio, $id)
    {
        $val1 = BalanceMensual::where('balancemensual.codcontable', '=', '3603')->where('balancemensual.codaniomes', '=', $anio)->where('balancemensual.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }
}
