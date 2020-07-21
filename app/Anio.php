<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Anio extends Model
{
    public $timestamps = false;
    protected $table = 'anio';
    protected $primaryKey = 'codanio';

    protected $fillable = ['codanio', 'nomanio'];

}
