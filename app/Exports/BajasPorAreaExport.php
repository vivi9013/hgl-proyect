<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class BajasPorAreaExport implements FromView, WithTitle, WithColumnWidths
{
    protected $bajasPorArea;
    protected $areaSeleccionada;
    protected $fechaInit;
    protected $fechaFin;

    /**
     * Constructor para recibir los datos necesarios para el reporte.
     *
     * @param  mixed  $bajasPorArea
     * @param  mixed  $areaSeleccionada
     * @param  mixed  $fechaInit
     * @param  mixed  $fechaFin
     */
    public function __construct($bajasPorArea, $areaSeleccionada = null, $fechaInit = null, $fechaFin = null)
    {
        $this->bajasPorArea     = $bajasPorArea;
        $this->areaSeleccionada = $areaSeleccionada;
        $this->fechaInit        = $fechaInit;
        $this->fechaFin         = $fechaFin;
    }

    /**
     * Renderiza la vista Blade que se convertirá en la hoja de cálculo XLSX.
     *
     * @return View
     */
    public function view(): View
    {
        return view('inventario.bajas_insumos.exportar_excel', [
            'bajasPorArea'     => $this->bajasPorArea,
            'areaSeleccionada' => $this->areaSeleccionada,
            'fechaInit'        => $this->fechaInit,
            'fechaFin'         => $this->fechaFin,
        ]);
    }

    /**
     * Nombre de la pestaña de la hoja de cálculo (máx. 31 caracteres).
     *
     * @return string
     */
    public function title(): string
    {
        return 'Reporte_Bajas_Por_Area_Asignada';
    }

    /**
     * Anchos definidos para cada columna del reporte.
     *
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 42,
            'B' => 10,
            'C' => 18,
            'D' => 14,
            'E' => 24,
            'F' => 18,
            'G' => 26,
            'H' => 26,
            'I' => 22,
        ];
    }
}
