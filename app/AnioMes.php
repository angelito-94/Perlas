<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnioMes extends Model
{
    public $timestamps = false;
    protected $table = 'aniomes';
    protected $primaryKey = 'codaniomes';

    protected $fillable = ['codaniomes', 'codanio', 'codmes'];

    public static function AnioMes($codanio)
    {
        return AnioMes::join('anio', 'anio.codanio', '=', 'aniomes.codanio')
            ->join('mes', 'mes.codmes', '=', 'aniomes.codmes')
            ->where('aniomes.codanio', '=', $codanio)->get();
    }

    public static function CodigoAnioMes($codaniomes)
    {
        return AnioMes::join('anio', 'anio.codanio', '=', 'aniomes.codanio')
            ->join('mes', 'mes.codmes', '=', 'aniomes.codmes')
            ->where('aniomes.codaniomes', '=', $codaniomes)->get();
    }
}
