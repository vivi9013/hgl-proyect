<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class DevolucionFormatoExport implements FromView, WithTitle, WithColumnWidths
{
    protected $devoluciones;
    protected $fechaInit;
    protected $fechaFin;

    /**
     * Constructor del export de formato de devoluciones.
     *
     * @param  mixed  $devoluciones
     * @param  string|null  $fechaInit
     * @param  string|null  $fechaFin
     */
    public function __construct($devoluciones, $fechaInit = null, $fechaFin = null)
    {
        $this->devoluciones = $devoluciones;
        $this->fechaInit    = $fechaInit;
        $this->fechaFin     = $fechaFin;
    }

    /**
     * Renderiza la vista Blade oficial del formato de devoluciones.
     *
     * @return View
     */
    public function view(): View
    {
        return view('inventario.devoluciones.exportar_excel', [
            'devoluciones' => $this->devoluciones,
            'fechaInit'    => $this->fechaInit,
            'fechaFin'     => $this->fechaFin,
        ]);
    }

    /**
     * Nombre de la pestaña de la hoja de cálculo (máx. 31 caracteres).
     *
     * @return string
     */
    public function title(): string
    {
        return 'Formato_Devolucion';
    }

    /**
     * Anchos definidos para cada columna del formato.
     *
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 18, // Clave
            'B' => 45, // Nombre del Material o Medicamento
            'C' => 14, // Cantidad
            'D' => 22, // Fecha de Caducidad
            'E' => 26, // Motivo
        ];
    }
}
