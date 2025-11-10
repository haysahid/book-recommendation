<?php

namespace App\Repositories;

use App\Models\Book;
use Illuminate\Support\Facades\Log;

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
        $book = Book::create($data);

        if (isset($data['category_slug'])) {
            $category = CategoryRepository::findCategoryBySlug($data['category_slug']);
            if ($category) {
                $book->categories()->attach($category->id);
            }
        }

        return $book;
    }

    static public function insertBooks($books)
    {
        foreach ($books as $bookData) {
            self::createBook($bookData);
        }
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

    static public function cleanTitle($book)
    {
        if ($book->cleaned_title) {
            return $book;
        }

        // Remove extra spaces, special characters, and convert to lowercase
        $cleanedTitle = preg_replace('/[^a-zA-Z0-9\s]/', '', $book->title);
        $cleanedTitle = strtolower(trim($cleanedTitle));

        // Remove stopwords
        $stopwordFactory = new \Sastrawi\StopWordRemover\StopWordRemoverFactory();
        $stopwordRemover = $stopwordFactory->createStopWordRemover();
        $titleWithoutStopwords = $stopwordRemover->remove($cleanedTitle);

        $book->cleaned_title = $titleWithoutStopwords;
        $book->save();
        return $book;
    }

    static public function cleanTitles($books)
    {
        $cleanedBooks = [];
        foreach ($books as $book) {
            $cleanedBooks[] = self::cleanTitle($book);
        }
        return $cleanedBooks;
    }

    static public function stemTitleWithSastrawi($book)
    {
        if ($book->stemmed_title) {
            return $book;
        }

        $stemmerFactory = new \Sastrawi\Stemmer\StemmerFactory();
        $stemmer = $stemmerFactory->createStemmer();

        // Perform stemming
        $stemmedTitle = $stemmer->stem($book->cleaned_title);

        $book->stemmed_title = $stemmedTitle;
        $book->save();
        return $book;
    }

    static public function stemTitlesWithSastrawi($books)
    {
        $stemmedBooks = [];
        foreach ($books as $book) {
            $stemmedBooks[] = self::stemTitleWithSastrawi($book);
        }
        return $stemmedBooks;
    }
}
