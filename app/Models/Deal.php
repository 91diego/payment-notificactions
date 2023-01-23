<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;
    protected $connection = 'mysql_portal_web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'deal_id',
        'lead_id',
        'real_state_development',
        'tower_acronym',
        'tower',
        'floor',
        'department',
        'delivery_date',
        'responsable',
        'status',
        'status_number',
        'payment_method',
        'user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'deal_id',
        'lead_id',
        'user_id',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the user that owns the Deal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
