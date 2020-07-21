<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EstadoResultados extends Model
{
    public $timestamps = false;
    protected $table = 'estadoresultadosanio';
    protected $primaryKey = 'codestadoresultadosanio';

    protected $fillable = ['codestadoresultadosanio', 'id', 'codanio', 'codcontable', 'nomcuenta', 'valorbalance'];

}
