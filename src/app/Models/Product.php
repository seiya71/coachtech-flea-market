<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_image',
        'product_name',
        'brand',
        'description',
        'price',
        'user_id',
        'category_id',
        'status'
    ];
}
