<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_image',
        'item_name',
        'brand',
        'description',
        'price',
        'user_id',
        'category_id',
        'condition',
        'sold',
    ];

    public function scopeSearchByName($query, $search)
    {
        if (!empty($search)) {
            $query->where('item_name', 'LIKE', "%{$search}%");
        }
        return $query;
    }

    protected $casts = [
        'sold' => 'boolean',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'item_category', 'item_id', 'category_id');
    }
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'likes', 'item_id', 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'item_id');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

}
