<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Semestre extends Model
{
    public $timestamps = false;
    protected $table = 'semestre';
    protected $primaryKey = 'codsemestre';

    protected $fillable = ['codsemestre', 'nomsemestre'];
}
