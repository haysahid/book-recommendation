<?php

namespace App\Repositories;

use App\Models\Book;
use Illuminate\Support\Facades\Log;

class BookRepository
{
    public static function getRecommendedBooks(
        $search = null,
        $limit = 10,
        $page = 1,
        $categorySlug = null,
        $excludeBookId = null,
    ) {
        if ($search) {
            $allBooks = Book::query();

            if ($categorySlug) {
                $allBooks->whereHas('categories', function ($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            }

            if ($excludeBookId) {
                $allBooks->where('id', '!=', $excludeBookId);
            }

            $allBooks = $allBooks->get();

            $allBooksTokens = [];
            foreach ($allBooks as $book) {
                // Check and preprocess titles if not already done
                if (!$book->cleaned_title) {
                    $book = self::cleanTitle($book);
                }

                if (!$book->stemmed_title) {
                    $book = self::stemTitleWithSastrawi($book);
                }

                $titleAndAuthor = $book->stemmed_title . ' ' . TfIdfRepository::cleanText($book->author);

                $tokens = TfIdfRepository::tokenize($titleAndAuthor);

                $allBooksTokens[] = $tokens;
            }

            list($df, $N) = TfIdfRepository::computeDf($allBooksTokens);

            $searchTokens = TfIdfRepository::preprocess($search);

            $booksWithScores = [];
            foreach ($allBooks as $book) {
                $titleAndAuthor = $book->stemmed_title . ' ' . TfIdfRepository::cleanText($book->author);
                $bookTokens = TfIdfRepository::tokenize($titleAndAuthor);

                $bookVectors = TfIdfRepository::buildTfIdfVector($bookTokens, $df, $N);
                $searchVectors = TfIdfRepository::buildTfIdfVector($searchTokens, $df, $N);

                $score = TfIdfRepository::cosineSimilarity(
                    $searchVectors,
                    $bookVectors
                );

                $book->score = $score;

                $booksWithScores[] = $book;
            }

            // Sort books by score
            usort($booksWithScores, fn($a, $b) => $b->score <=> $a->score);

            $offset = ($page - 1) * $limit;
            $paginatedBooks = array_slice($booksWithScores, $offset, $limit);

            return new \Illuminate\Pagination\LengthAwarePaginator(
                $paginatedBooks,
                count($booksWithScores),
                $limit,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        $query = Book::query();

        if ($categorySlug) {
            $query->whereHas('categories', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        $query->orderBy('created_at', 'desc');
        return $query->paginate($limit);
    }

    public static function getBooks(
        $search = null,
        $limit = 10,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
        $categorySlug = null
    ) {
        $query = Book::query();

        if ($categorySlug) {
            $query->whereHas('categories', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        if ($search) {
            $query->where('title', 'like', '%' . $search . '%')
                ->orWhere('author', 'like', '%' . $search . '%');
        }

        $query->orderBy($orderBy, $orderDirection);

        return $query->paginate($limit);
    }

    public static function getRelatedBooks($book, $limit = 5)
    {
        $titleAndAuthor = $book->title . ' ' . TfIdfRepository::cleanText($book->author);

        $relatedBooks = self::getRecommendedBooks(
            search: $titleAndAuthor,
            limit: $limit,
            excludeBookId: $book->id,
        );

        // Remove paginaton
        $relatedBooks = $relatedBooks->items();

        return $relatedBooks;
    }

    public static function findBookBySlug($slug)
    {
        return Book::where('slug', $slug)->first();
    }

    public static function createBook($data)
    {
        $existingBook = self::findBookBySlug($data['slug']);

        if (!$existingBook) {
            $book = Book::create($data);
        } else {
            $book = $existingBook;
        }

        if (!empty($data['category_slug'])) {
            $category = CategoryRepository::findCategoryBySlug($data['category_slug']);
            if ($category) {
                $book->categories()->syncWithoutDetaching([$category->id]);
            }
        }

        return $book;
    }

    public static function insertBooks($books)
    {
        foreach ($books as $bookData) {
            self::createBook($bookData);
        }
    }

    public static function updateBook($book, $data)
    {
        $book->update($data);
        return $book;
    }

    public static function deleteBook($book)
    {
        return $book->delete();
    }

    public static function cleanTitle($book)
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

    public static function cleanTitles($books)
    {
        $cleanedBooks = [];
        foreach ($books as $book) {
            $cleanedBooks[] = self::cleanTitle($book);
        }
        return $cleanedBooks;
    }

    public static function stemTitleWithSastrawi($book)
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

    public static function stemTitlesWithSastrawi($books)
    {
        $stemmedBooks = [];
        foreach ($books as $book) {
            $stemmedBooks[] = self::stemTitleWithSastrawi($book);
        }
        return $stemmedBooks;
    }
}
