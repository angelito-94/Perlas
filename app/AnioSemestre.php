<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnioSemestre extends Model
{
     public $timestamps = false;
    protected $table = 'aniosemestre';
    protected $primaryKey = 'codaniosemestre';

    protected $fillable = ['codaniosemestre', 'codanio', 'codsemestre'];

    public static function AnioSemestre($codanio)
    {
        return AnioSemestre::join('anio', 'anio.codanio', '=', 'aniosemestre.codanio')
            ->join('semestre', 'semestre.codsemestre', '=', 'aniosemestre.codsemestre')
            ->where('aniosemestre.codanio', '=', $codanio)->get();
    }

    public static function CodigoAnioSemestre($codaniosemestre)
    {
        return AnioSemestre::join('anio', 'anio.codanio', '=', 'aniosemestre.codanio')
            ->join('semestre', 'semestre.codsemestre', '=', 'aniosemestre.codsemestre')
            ->where('aniosemestre.codaniosemestre', '=', $codaniosemestre)->get();
    }
}
