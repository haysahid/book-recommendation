<?php

namespace App\Exports;

use App\Models\Book;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BooksExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Book::all()->map(function ($book, $index) {
            return [
                'no' => $index + 1,
                'id' => $book->id,
                'title' => $book->title,
                'image' => $book->image,
                'slug' => $book->slug,
                'author' => $book->author,
                'final_price' => $book->final_price,
                'slice_price' => $book->slice_price,
                'discount' => $book->discount,
                'is_oos' => $book->is_oos,
                'sku' => $book->sku,
                'format' => $book->format,
                'store_name' => $book->store_name,
                'isbn' => $book->isbn,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'no',
            'id',
            'title',
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
            'isbn'
        ];
    }
}
