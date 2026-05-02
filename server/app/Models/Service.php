<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'base_price', 'description'];

    public function filials()
    {
        return $this->belongsToMany(Filial::class)->withPivot('is_active')->withTimestamps();
    }
}
