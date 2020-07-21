<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    public $timestamps = false;
    protected $table = 'balanceanio';
    protected $primaryKey = 'codbalanceanio';

    protected $fillable = ['codbalanceanio', 'id', 'codanio', 'codcontable', 'nomcuenta', 'valorbalance'];

    public static function BalanceAnual($codanio, $id)
    {
        return Balance::join('anio', 'anio.codanio', '=', 'balanceanio.codanio')
            ->where('balanceanio.codanio', '=', $codanio)
            ->where('balanceanio.id', '=', $id)->get();
    }

    public static function eliminarbalanceanual($codanio, $id)
    {
        Balance::where('balanceanio.codanio', '=', $codanio)
            ->where('balanceanio.id', '=', $id)->delete();
    }

    public static function balance($codanio, $id, $codcontable)
    {
        return Balance::where('balanceanio.codanio', '=', $codanio)
            ->where('balanceanio.id', '=', $id)
            ->where('balanceanio.codcontable', '=', $codcontable)->first();
    }

    public static function listaranios($id)
    {
        return Balance::distinct()->select('balanceanio.codanio','anio.nomanio')
        ->join('anio','anio.codanio','=','balanceanio.codanio')
        ->where('balanceanio.id', '=', $id)->get(); 
    }

    public static function carteraporvencer($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '1402')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        $val2 = Balance::where('balanceanio.codcontable', '=', '1403')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        $val3 = Balance::where('balanceanio.codcontable', '=', '1404')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        $val4 = Balance::where('balanceanio.codcontable', '=', '1405')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1) && empty($val2) && empty($val3) && empty($val4)) {
            return 0;
        } else {
            $valor = $val1->valorbalance + $val2->valorbalance + $val3->valorbalance + $val4->valorbalance;
            return $valor;
        }
    }

    public static function carteraquenodevengaintereses($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '1426')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        $val2 = Balance::where('balanceanio.codcontable', '=', '1427')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        $val3 = Balance::where('balanceanio.codcontable', '=', '1428')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1) && empty($val2) && empty($val3)) {
            return 0;
        } else {
            $valor = $val1->valorbalance + $val2->valorbalance + $val3->valorbalance;
            return $valor;
        }
    }

    public static function provisioncartera($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '149910')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        $val2 = Balance::where('balanceanio.codcontable', '=', '149915')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        $val3 = Balance::where('balanceanio.codcontable', '=', '149920')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        $val4 = Balance::where('balanceanio.codcontable', '=', '149925')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        $val5 = Balance::where('balanceanio.codcontable', '=', '149980')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
                
        if (empty($val1) && empty($val2) && empty($val3) && empty($val4) && empty($val5)) {
            return 0;
        } else {
            $valor = $val1->valorbalance + $val2->valorbalance + $val3->valorbalance + $val4->valorbalance + $val5->valorbalance;
            return $valor;
        }
    }

    public static function carteravencida($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '1450')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        $val2 = Balance::where('balanceanio.codcontable', '=', '1451')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        $val3 = Balance::where('balanceanio.codcontable', '=', '1452')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1) && empty($val2) && empty($val3)) {
            return 0;
        } else {
            $valor = $val1->valorbalance + $val2->valorbalance + $val3->valorbalance;
            return $valor;
        }
    }

    public static function proviciones($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '1499')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function cuentasporcobrar($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '16')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function bienesendaciondepago($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '17')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function activosfijos($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '19')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function otrosactivos($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '19')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function depositosdesocios($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '21')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function obligacionesfinancieras($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '26')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function reservastotales($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '3')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        $val2 = Balance::where('balanceanio.codcontable', '=', '31')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        
        if (empty($val1 && empty($val2))) {
            return 0;
        } else {
            $valor = $val1->valorbalance - $val2->valorbalance;
            return $valor;
        }
    }

    public static function depositosalavista($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '2101')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function de1a30dias($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '210305')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function de31a90dias($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '210310')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function oblde1a30dias($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '260405')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        $val2 = Balance::where('balanceanio.codcontable', '=', '260605')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        
        if (empty($val1 && empty($val2))) {
            return 0;
        } else {
            $valor = $val1->valorbalance + $val2->valorbalance;
            return $valor;
        }
    }

    public static function oblde31a90dias($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '260410')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        $val2 = Balance::where('balanceanio.codcontable', '=', '260610')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        
        if (empty($val1 && empty($val2))) {
            return 0;
        } else {
            $valor = $val1->valorbalance - $val2->valorbalance;
            return $valor;
        }
    }

    public static function inversiones($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '13')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function totalpasivo($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '2')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function totalactivo($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '1')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function totalpatrimonio($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '3')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function capitalsocial($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '31')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function interesesydescuentosdeinversionesentitulosvalores($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '5103')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function interesesydescuentosdecarteradecreditos($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '5104')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function interesesydescuentosganados($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '51')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function obligacionesconelpublico($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '21')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function depositosdeahorro($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '410115')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function depositosaplazo($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '410130')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function depositosaplazo2($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '2103')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function obligacionesfinancieras4103($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '4103')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function interesescausados($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '41')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function gastosoperacion($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '45')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function ingresosporservicios($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '54')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function gastosdepersonal($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '4501')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }
    
    public static function perdidasyganancias($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '59')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 0;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function fondosdisponibles($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '11')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 1; 
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }

    public static function utilidadoexecedentedelejericicio($anio, $id)
    {
        $val1 = Balance::where('balanceanio.codcontable', '=', '3603')->where('balanceanio.codanio', '=', $anio)->where('balanceanio.id', '=', $id)->first();
        if (empty($val1)) {
            return 1;
        } else {
            $valor = $val1->valorbalance;
            return $valor;
        }
    }
    
}
