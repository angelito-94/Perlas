<?php

namespace App\Imports;

use App\Balance;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;

class BalanceExport implements ToModel
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
        return new Balance([
            'id' => $this->id,
            'codanio' => $this->codanio,
            'codcontable' => $row[0],
            'nomcuenta' => $row[1],
            'valorbalance' => round($row[2],2),
        ]);
    }
}
