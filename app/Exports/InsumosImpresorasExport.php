<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class InsumosImpresorasExport implements FromView, WithTitle, WithColumnWidths
{
    protected $insumos;

    public function __construct($insumos)
    {
        $this->insumos = $insumos;
    }

    public function view(): View
    {
        return view('control_insumos.insumos_impresoras.exportar_excel', [
            'insumos' => $this->insumos,
        ]);
    }

    public function title(): string
    {
        return 'Catalogo_Insumos';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 30,
            'C' => 15,
            'D' => 35,
            'E' => 18,
            'F' => 18,
            'G' => 12,
            'H' => 12,
        ];
    }
}
