<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filial extends Model
{
    protected $fillable = ['address', 'city', 'phone', 'is_active'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class)->withPivot('is_active')->withTimestamps();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity')->withTimestamps();
    }
}