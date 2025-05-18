<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
<<<<<<< HEAD
        'name', 'bought_price', 'sale_price', 'quantity', 'category_id', 'has_expiry', 'expiry_date'
=======
        'name', 'bought_price', 'sale_price', 'quantity', 'category_id', 'has_expiry', 'expiry_date', 'stock_threshold'
>>>>>>> responsivness
    ];
    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function sales() {
        return $this->hasMany(Sale::class);
    }
}
