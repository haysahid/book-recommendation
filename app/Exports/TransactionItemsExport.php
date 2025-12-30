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
        return TransactionItem::all()->map(function ($book, $index) {
            return [
                'no' => $index + 1,
                'store_id' => $book->store_id,
                'transaction_id' => $book->transaction_id,
                'book_id' => $book->book_id,
                'quantity' => $book->quantity,
                'unit_base_price' => $book->unit_base_price,
                'unit_discount_type' => $book->unit_discount_type,
                'unit_discount' => $book->unit_discount,
                'unit_final_price' => $book->unit_final_price,
                'subtotal' => $book->subtotal,
                'fullfillment_status' => $book->fullfillment_status,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'no',
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
