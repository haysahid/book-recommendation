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
                'title' => $book->title,
                'image' => $book->image,
                'slug' => $book->slug,
                'author' => $book->author,
                'store_name' => $book->store_name,
                'isbn' => $book->isbn,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'no',
            'title',
            'image',
            'slug',
            'author',
            'store_name',
            'isbn'
        ];
    }
}
