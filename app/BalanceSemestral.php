<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class balancesemestral extends Model
{
    public $timestamps = false;
    protected $table = 'balancesemestral';
    protected $primaryKey = 'codbalancesemestral';

    protected $fillable = ['codbalancesemestral', 'id', 'codaniosemestral', 'codcontable', 'nomcuenta', 'valorbalance'];

    public static function balancesemestral($codaniosemestral, $id)
    {
        return balancesemestral::join('aniosemestre', 'aniosemestre.codaniosemestre', '=', 'balancesemestral.codaniosemestral')
            ->where('balancesemestral.codaniosemestral', '=', $codaniosemestral)
            ->where('balancesemestral.id', '=', $id)->get();
    }

    public static function eliminarbalancesemestral($codaniosemestral, $id)
    {
        balancesemestral::where('balancesemestral.codaniosemestral', '=', $codaniosemestral)
            ->where('balancesemestral.id', '=', $id)->delete();
    }

    public static function balance($codaniosemestral, $id, $codcontable)
    {
        return balancesemestral::where('balancesemestral.codaniosemestral', '=', $codaniosemestral)
            ->where('balancesemestral.id', '=', $id)
            ->where('balancesemestral.codcontable', '=', $codcontable)->first();
    }

    public static function listaranios($id)
    {
        return balancesemestral::distinct()->select('balancesemestral.codaniosemestral', 'anio.nomanio', 'semestre.nomsemestre')
            ->join('aniosemestre', 'aniosemestre.codaniosemestre', '=', 'balancesemestral.codaniosemestral')
            ->join('anio', 'anio.codanio', '=', 'aniosemestre.codanio')
            ->join('semestre', 'semestre.codsemestre', '=', 'aniosemestre.codsemestre')
            ->where('balancesemestral.id', '=', $id)->get();
    }

    public static function carteraporvencer($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '1402')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        $val2 = balancesemestral::where('balancesemestral.codcontable', '=', '1403')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        $val3 = balancesemestral::where('balancesemestral.codcontable', '=', '1404')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1) && empty($val2) && empty($val3)) {
            return 0;
        } else {
            $valor = $val1->valorbalance + $val2->valorbalance + $val3->valorbalance;
            return $valor;
        }
    }

    public static function carteraquenodevengaintereses($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '1426')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        $val2 = balancesemestral::where('balancesemestral.codcontable', '=', '1427')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        $val3 = balancesemestral::where('balancesemestral.codcontable', '=', '1428')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1) && empty($val2) && empty($val3)) {
            return 0;
        } else {
            $valor = $val1->valorbalance + $val2->valorbalance + $val3->valorbalance;
            return $valor;
        }
    }

    public static function carteravencida($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '1450')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        $val2 = balancesemestral::where('balancesemestral.codcontable', '=', '1451')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        $val3 = balancesemestral::where('balancesemestral.codcontable', '=', '1452')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1) && empty($val2) && empty($val3)) {
            return 0;
        } else {
            $valor = $val1->valorbalance + $val2->valorbalance + $val3->valorbalance;
            return $valor;
        }
    }

    public static function proviciones($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '1499')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function cuentasporcobrar($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '16')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function bienesendaciondepago($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '17')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function activosfijos($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '19')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function otrosactivos($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '19')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function depositosdesocios($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '21')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function obligacionesfinancieras($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '26')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function reservastotales($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '3')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        $val2 = balancesemestral::where('balancesemestral.codcontable', '=', '31')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();

        if (empty($val1 && empty($val2))) {
            return 0;
        } else {
            $valor = $val1->valorbalance - $val2->valorbalance;
            return $valor;
        }
    }

    public static function depositosalavista($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '2101')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function de1a30dias($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '210305')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function de31a90dias($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '210310')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function oblde1a30dias($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '260405')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        $val2 = balancesemestral::where('balancesemestral.codcontable', '=', '260605')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();

        if (empty($val1 && empty($val2))) {
            return 0;
        } else {
            $valor = $val1->valorbalance + $val2->valorbalance;
            return $valor;
        }
    }

    public static function oblde31a90dias($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '260410')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        $val2 = balancesemestral::where('balancesemestral.codcontable', '=', '260610')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();

        if (empty($val1 && empty($val2))) {
            return 0;
        } else {
            $valor = $val1->valorbalance - $val2->valorbalance;
            return $valor;
        }
    }

    public static function inversiones($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '13')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function totalpasivo($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '2')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function totalactivo($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '1')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function totalpatrimonio($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '3')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function capitalsocial($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '31')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function interesesydescuentosdeinversionesentitulosvalores($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '5103')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function interesesydescuentosdecarteradecreditos($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '5104')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function interesesydescuentosganados($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '51')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function obligacionesconelpublico($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '21')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function depositosdeahorro($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '410115')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function depositosaplazo($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '410130')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function depositosaplazo2($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '2103')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function obligacionesfinancieras4103($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '4103')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function interesescausados($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '41')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function gastosoperacion($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '45')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function ingresosporservicios($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '54')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function gastosdepersonal($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '4501')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function perdidasyganancias($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '59')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function fondosdisponibles($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '11')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function utilidadoexecedentedelejericicio($anio, $id)
    {
        $val1 = balancesemestral::where('balancesemestral.codcontable', '=', '3603')->where('balancesemestral.codaniosemestral', '=', $anio)->where('balancesemestral.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }
}
