<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class ComercialReportExport implements FromArray, WithHeadings
{

    protected $invoices;

    public function __construct(array $invoices)
    {
        $this->invoices = $invoices;
    }
    /**
    * @return \Illuminate\Support\Array
    */
    public function array(): array
    {
        return $this->invoices;
    }

    public function headings(): array
    {
        return [
            'ID NEGOCIACION',
            'ID PROSPECTO',
            'TITULO NEGOCIACION',
            'NOMBRE CONTACTO',
            'TELEFONO CONTACTO',
            'EMAIL CONTACTO',
            'ETAPA',
            'ASESOR RESPONSABLE',
            'ORIGEN NEGOCIACION',
            'DESARROLLO',
            'FECHA Y HORA EXACTA DE VISITA',
            'ESTATUS VISITA',
            'FECHA CREACION NEGOCIACION',
            'FECHA MODIFICACION NEGOCIACION',
            'COMENTARIO(S)',
            'ACTIVIDAD(ES)'
        ];
    }
}
