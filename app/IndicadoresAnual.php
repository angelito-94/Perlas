<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IndicadoresAnual extends Model
{
    public $timestamps = false;
    protected $table = 'indicadoresanual';
    protected $primaryKey = 'codindicadoresanual';

    protected $fillable = ['codindicadoresanual', 'codindicador', 'id', 'codanio', 'nomindicador', 'forindicador', 'calindicador', 'valorindicador','metindicador'];

    public static function ExisteIndicadoresAnual($codanio, $id)
    {
        return IndicadoresAnual::join('anio', 'anio.codanio', '=', 'indicadoresanual.codanio')
            ->where('indicadoresanual.codanio', '=', $codanio)
            ->where('indicadoresanual.id', '=', $id)->first();
    }

    public static function Indicador($codindicador, $codanio, $id)
    {
        return IndicadoresAnual::join('anio', 'anio.codanio', '=', 'indicadoresanual.codanio')
            ->where('indicadoresanual.codindicador', '=', $codindicador)
            ->where('indicadoresanual.codanio', '=', $codanio)
            ->where('indicadoresanual.id', '=', $id)->first();
    }

    public static function Indicadores($codanio, $id)
    {
        return IndicadoresAnual::join('anio', 'anio.codanio', '=', 'indicadoresanual.codanio')
            ->where('indicadoresanual.codanio', '=', $codanio)
            ->where('indicadoresanual.id', '=', $id)->get();
    }
}
