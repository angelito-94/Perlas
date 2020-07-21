<?php

namespace App\Imports;

use App\BalanceMensual;
use Maatwebsite\Excel\Concerns\ToModel;

class BalanceMensualImport implements ToModel
{
    private $codanio;  
    private $id;

    public function __construct(int $codanio, int $id)
    {
        $this->codanio = $codanio; 
        $this->id = $id; 
    } 

    public function model(array $row)
    {
        return new BalanceMensual([ 
            'id' => $this->id,
            'codaniomes' => $this->codanio,
            'codcontable' => $row[0],
            'nomcuenta' => $row[1],
            'valorbalance' => round($row[2],2),
        ]);
    }
}
