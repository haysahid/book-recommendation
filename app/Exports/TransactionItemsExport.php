<?php

namespace App\Exports;

use App\Models\Book;
use App\Models\TransactionItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionItemsExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return TransactionItem::with(['transaction'])->get()->map(function ($item, $index) {
            return [
                'no' => $index + 1,
                'id' => $item->id,
                'user_id' => $item->user_id,
                'store_id' => $item->store_id,
                'transaction_id' => $item->transaction_id,
                'book_id' => $item->book_id,
                'quantity' => $item->quantity,
                'unit_base_price' => $item->unit_base_price,
                'unit_discount_type' => $item->unit_discount_type,
                'unit_discount' => $item->unit_discount,
                'unit_final_price' => $item->unit_final_price,
                'subtotal' => $item->subtotal,
                'fullfillment_status' => $item->fullfillment_status,
                'rating' => $item->rating,
                'review' => $item->review,
                'created_at' => $item->created_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'no',
            'id',
            'user_id',
            'store_id',
            'transaction_id',
            'book_id',
            'quantity',
            'unit_base_price',
            'unit_discount_type',
            'unit_discount',
            'unit_final_price',
            'subtotal',
            'fullfillment_status',
        ];
    }
}
