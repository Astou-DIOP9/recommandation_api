<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class products extends Model
{
    protected $fillable = [
        'nom',
        'description',
        'price',
        'category_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function orderItems()
    {
        return $this->hasMany(order_items::class, 'product_id');
    }

    public function views()
    {
        return $this->hasMany(product_views::class, 'product_id');
    }

    public function category()
    {
        return $this->belongsTo(categories::class, 'category_id');
    }
}
