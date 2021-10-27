<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealSell extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'negociacion_bitrix_id',
        'prospecto_bitrix_id',
        'negociacion_venta_bitrix_id',
        'etapa',
        'tipo',
        'gerente',
        'responsable',
        'origen',
        'motivo_compra',
        'canal_venta',
        'producto',
        'precio',
        'motivo_descalificacion',
        'motivo_cancelacion_apartado',
        'desarrollo',
        'desarrollo_interes',
        'tipo_visita',
        'negociacion_descalificado_comentarios',
        'hora_exacta_visita',
        'apartado_el',
        'vendido_el',
        'compromiso_entrega_el',
        'compromiso_entrega_reproyectado_el',
        'bitrix_creado_el',
        'bitrix_modificado_el',
    ];
}
