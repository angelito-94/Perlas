<?php

namespace App\Imports;

use App\BalanceSemestral;
use Maatwebsite\Excel\Concerns\ToModel;

class BalanceSemestralImport implements ToModel
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
        return new BalanceSemestral([
            'id' => $this->id,
            'codaniosemestral' => $this->codanio,
            'codcontable' => $row[0],
            'nomcuenta' => $row[1],
            'valorbalance' => round($row[2],2),
        ]);
    }
}
