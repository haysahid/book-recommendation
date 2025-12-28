<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'translated_title',
        'cleaned_title',
        'stemmed_title',
        'image',
        'slug',
        'author',
        'final_price',
        'slice_price',
        'discount',
        'is_oos',
        'sku',
        'format',
        'store_name',
        'isbn',
    ];

    protected $casts = [
        'is_oos' => 'boolean',
    ];

    // Additional attributes
    public function getUrlAttribute()
    {
        return url('/book/' . $this->slug);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'book_category', 'book_id', 'category_id');
    }

    public function transaction_items()
    {
        return $this->hasMany(TransactionItem::class, 'book_id', 'id');
    }
}
