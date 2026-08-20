<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class MovimientosInsumosExport implements FromView, WithTitle, WithColumnWidths
{
    protected $movimientos;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($movimientos, $fechaInicio = null, $fechaFin = null)
    {
        $this->movimientos = $movimientos;
        $this->fechaInicio  = $fechaInicio;
        $this->fechaFin     = $fechaFin;
    }

    public function view(): View
    {
        return view('control_insumos.movimientos_insumos.exportar_excel', [
            'movimientos' => $this->movimientos,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin'    => $this->fechaFin,
        ]);
    }

    public function title(): string
    {
        return 'Movimientos_Insumos';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 45,
            'B' => 15,
            'C' => 15,
        ];
    }
}
