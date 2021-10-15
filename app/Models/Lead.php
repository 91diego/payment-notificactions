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
        'bitrix_id',
        'name',
        'phone',
        'email',
        'origin',
        'responsable',
        'purchase_reason',
        'sales_channel',
        'development',
        'disqualification_reason',
        'status',
        'bitrix_created_by',
        'bitrix_created_at',
        'bitrix_modified_at',
    ];
}
