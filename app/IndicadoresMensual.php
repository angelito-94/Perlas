<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IndicadoresMensual extends Model
{
    public $timestamps = false;
    protected $table = 'indicadoresmensual';
    protected $primaryKey = 'codindicadoresmensual';

    protected $fillable = ['codindicadoresmensual', 'codindicador', 'id', 'codaniomes', 'nomindicador', 'forindicador', 'calindicador', 'valorindicador', 'metindicador'];

    public static function Existeindicadoresmensual($codaniomes, $id)
    {
        return IndicadoresMensual::join('aniomes', 'aniomes.codaniomes', '=', 'indicadoresmensual.codaniomes')
            ->join('anio', 'anio.codanio', '=', 'aniomes.codanio')
            ->where('indicadoresmensual.codaniomes', '=', $codaniomes)
            ->where('indicadoresmensual.id', '=', $id)->first();
    }

    public static function Indicador($codindicador, $codaniomes, $id)
    {
        return IndicadoresMensual::join('aniomes', 'aniomes.codaniomes', '=', 'indicadoresmensual.codaniomes')
            ->join('anio', 'anio.codanio', '=', 'aniomes.codanio')
            ->join('mes','mes.codmes','=','aniomes.codmes')
            ->where('indicadoresmensual.codindicador', '=', $codindicador)
            ->where('indicadoresmensual.codaniomes', '=', $codaniomes)
            ->where('indicadoresmensual.id', '=', $id)->first();
    }

    public static function Indicadores($codaniomes, $id)
    {
        return IndicadoresMensual::join('aniomes', 'aniomes.codaniomes', '=', 'indicadoresmensual.codaniomes')
            ->join('anio', 'anio.codanio', '=', 'aniomes.codanio')
            ->join('mes','mes.codmes','=','aniomes.codmes')
            ->where('indicadoresmensual.codaniomes', '=', $codaniomes)
            ->where('indicadoresmensual.id', '=', $id)->get();
    }
}
