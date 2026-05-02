<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'filial_id', 
        'status', 
        'client_name', 
        'client_phone', 
        'vin_code', 
        'total_price', 
        'scheduled_date', 
        'admin_comment'
    ];

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}