<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IndicadoresSemestral extends Model
{
    public $timestamps = false;
    protected $table = 'indicadoressemestral';
    protected $primaryKey = 'codindicadoressemestral';

    protected $fillable = ['codindicadoressemestral', 'codindicador', 'id', 'codaniosemestral', 'nomindicador', 'forindicador', 'calindicador', 'valorindicador', 'metindicador'];

    public static function Existeindicadoressemestral($codsemestral, $id)
    {
        return IndicadoresSemestral::join('aniosemestre', 'aniosemestre.codaniosemestre', '=', 'indicadoressemestral.codaniosemestral')
            ->join('anio', 'anio.codanio', '=', 'aniosemestre.codanio')
            ->join('semestre','semestre.codsemestre','=','aniosemestre.codsemestre')
            ->where('indicadoressemestral.codaniosemestral', '=', $codsemestral)
            ->where('indicadoressemestral.id', '=', $id)->first();
    }

    public static function Indicador($codindicador, $codsemestral, $id)
    {
        return IndicadoresSemestral::join('aniosemestre', 'aniosemestre.codaniosemestre', '=', 'indicadoressemestral.codaniosemestral')
            ->join('anio', 'anio.codanio', '=', 'aniosemestre.codanio')
            ->join('semestre','semestre.codsemestre','=','aniosemestre.codsemestre')
            ->where('indicadoressemestral.codindicador', '=', $codindicador)
            ->where('indicadoressemestral.codaniosemestral', '=', $codsemestral)
            ->where('indicadoressemestral.id', '=', $id)->first();
    }

    public static function Indicadores($codsemestral, $id)
    {
        return IndicadoresSemestral::join('aniosemestre', 'aniosemestre.codaniosemestre', '=', 'indicadoressemestral.codaniosemestral')
            ->join('anio', 'anio.codanio', '=', 'aniosemestre.codanio')
            ->join('semestre','semestre.codsemestre','=','aniosemestre.codsemestre')
            ->where('indicadoressemestral.codaniosemestral', '=', $codsemestral)
            ->where('indicadoressemestral.id', '=', $id)->get();
    }
}
