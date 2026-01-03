<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionItem extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'store_id',
        'transaction_id',
        'user_id',
        'book_id',
        'quantity',
        'unit_base_price',
        'unit_discount_type',
        'unit_discount',
        'unit_final_price',
        'subtotal',
        'fullfillment_status',
        'rating',
        'review',
        'reviewed_at',
    ];

    protected $appends = [
        'image'
    ];

    protected function getImageAttribute()
    {
        return $this->book->image ?? null;
    }

    // Relationships
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function attachments()
    {
        return $this->hasMany(ReviewAttachment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
