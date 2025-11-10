<?php

namespace App\Repositories;

use App\Models\Book;

class BookRepository
{
    static public function getBooks(
        $search = null,
        $limit = 10,
        $orderBy = 'created_at',
        $orderDirection = 'desc'
    ) {
        $query = Book::query();

        if ($search) {
            $query->where('title', 'like', '%' . $search . '%')
                ->orWhere('author', 'like', '%' . $search . '%');
        }

        $query->orderBy($orderBy, $orderDirection);

        return $query->paginate($limit);
    }

    static public function findBookBySlug($slug)
    {
        return Book::where('slug', $slug)->first();
    }

    static public function createBook($data)
    {
        return Book::create($data);
    }

    static public function updateBook($book, $data)
    {
        $book->update($data);
        return $book;
    }

    static public function deleteBook($book)
    {
        return $book->delete();
    }
}
