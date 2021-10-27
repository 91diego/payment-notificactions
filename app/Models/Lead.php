<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'prospecto_bitrix_id',
        'nombre',
        'telefono',
        'email',
        'origen',
        'responsable',
        'motivo_compra',
        'canal_ventas',
        'desarrollo',
        'motivo_descalificacion',
        'estatus',
        'bitrix_creado_por',
        'bitrix_creado_el',
        'bitrix_modificado_el',
    ];
}
