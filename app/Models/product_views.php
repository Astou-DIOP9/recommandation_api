<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class product_views extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'session_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(products::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
