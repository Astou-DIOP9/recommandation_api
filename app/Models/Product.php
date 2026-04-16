<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'title',
        'description',
        'price',
        'discount_price',
        'image',
        'in_stock',
        'category_id',
    ];

    protected $casts = [
        'price'          => 'integer',
        'discount_price' => 'integer',
        'in_stock'       => 'boolean',
    ];

    protected $appends = ['price_fcfa'];

    public function getPriceFcfaAttribute(): string
    {
        return number_format($this->price, 0, ',', ' ') . ' FCFA';
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    public function views()
    {
        return $this->hasMany(ProductView::class, 'product_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
