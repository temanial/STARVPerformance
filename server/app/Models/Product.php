<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 
        'name', 
        'brand', 
        'price', 
        'stock_quantity', 
        'allow_preorder', 
        'description', 
        'image_path'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function filials()
    {
        return $this->belongsToMany(Filial::class)->withPivot('quantity')->withTimestamps();
    }
}