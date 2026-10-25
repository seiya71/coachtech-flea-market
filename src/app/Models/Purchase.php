<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';

    protected $fillable = [
        'user_id',
        'item_id',
        'shipping_postal_code',
        'shipping_address',
        'shipping_building_name',
        'stripe_session_id',
        'status',
    ];

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
