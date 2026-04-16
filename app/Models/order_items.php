<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class order_items extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantite',
    ];

    protected $casts = [
        'quantite' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(order::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(products::class, 'product_id');
    }
}
