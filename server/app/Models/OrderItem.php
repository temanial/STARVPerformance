<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 
        'item_type', 
        'item_id', 
        'title_at_time', 
        'price_at_time', 
        'quantity'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
