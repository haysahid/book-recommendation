<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookCategory extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'image',
        'parent_id',
    ];

    public function books()
    {
        return $this->hasMany(BookCategory::class, 'category_id');
    }

    public function categories()
    {
        return $this->hasMany(BookCategory::class, 'parent_id');
    }
}
