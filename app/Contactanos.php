<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contactanos extends Model
{
    public $timestamps = false;
    protected $table = 'contactanos';
    protected $primaryKey = 'idcontactanos';

    protected $fillable = ['idcontactanos', 'nombre', 'apelllido', 'email', 'mensaje'];
}
