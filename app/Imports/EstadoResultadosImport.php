<?php

namespace App\Imports;

use App\EstadoResultados;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;

class EstadoResultadosImport implements ToModel
{
    private $codanio; 

    public function __construct(int $codanio )
    {
        $this->codanio = $codanio; 
    } 

    public function model(array $row)
    {
        return new EstadoResultados([
            'id' => Auth::id(),
            'codanio' => $this->codanio,
            'codcontable' => $row[0],
            'nomcuenta' => $row[1],
            'valorbalance' => $row[2],
        ]);
    }
}
