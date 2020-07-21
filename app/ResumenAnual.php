<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResumenAnual extends Model
{
    public $timestamps = false;
    protected $table = 'resumenanual';
    protected $primaryKey = 'codresumenanual';

    protected $fillable = ['codresumenanual', 'id', 'codanio', 'nomcuenta', 'valorbalance'];
}
