<?php

namespace App\Repositories;

use App\Models\Book;
use App\Models\ReviewAttachmentFileType;
use App\Models\TransactionItem;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BookRepository
{
    public static function getUserRecommendedBooks($user, $limit = 10)
    {
        $client = new Client();
        try {
            $response = $client->get(
                env('RECOMMENDATION_SYSTEM_API_URL') . "/recommend/{$user->id}",
                [
                    'query' => [
                        'limit' => $limit,
                    ],
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);

            $results = $responseData['results'];

            $recommendedBooks = Book::selectRaw('books.*, COALESCE(CAST(AVG(transaction_items.rating) AS FLOAT), 0) as average_rating')
                ->selectRaw('COALESCE(CAST(SUM(CASE WHEN transaction_items.rating > 0 THEN 1 ELSE 0 END) AS FLOAT), 0) as rating_count')
                ->selectRaw('COALESCE(SUM(transaction_items.quantity), 0) as sold')
                ->leftJoin('transaction_items', 'books.id', '=', 'transaction_items.book_id')
                ->groupBy('books.id')
                ->whereIn('books.id', array_column($results, 'id'))->get();

            // Sort books according to the order of recommendedBookIds
            $recommendedBooks = $recommendedBooks->sortBy(function ($book) use ($results) {
                // Include the score and reason
                foreach ($results as $result) {
                    if ($result['id'] == $book->id) {
                        $book->score = $result['score'] ?? null;
                        $book->reason = $result['reason'] ?? null;
                        return array_search($result, $results);
                    }
                }
                return PHP_INT_MAX; // If not found, put it at the end
            })->values();

            return [
                ...$responseData,
                'results' => $recommendedBooks,
            ];
        } catch (Exception $e) {
            Log::error('Error fetching user recommended books: ' . $e->getMessage());
            return collect();
        }
    }

    public static function getRecommendedBooks(
        $search = null,
        $limit = 10,
        $page = 1,
        $categorySlug = null,
        $excludeBookId = null,
    ) {
        if ($search) {
            $allBooks = Book::query()
                ->selectRaw('books.*, COALESCE(CAST(AVG(transaction_items.rating) AS FLOAT), 0) as average_rating')
                ->selectRaw('COALESCE(CAST(SUM(CASE WHEN transaction_items.rating > 0 THEN 1 ELSE 0 END) AS FLOAT), 0) as rating_count')
                ->selectRaw('COALESCE(SUM(transaction_items.quantity), 0) as sold')
                ->leftJoin('transaction_items', 'books.id', '=', 'transaction_items.book_id')
                ->groupBy('books.id');

            if ($categorySlug) {
                $allBooks->whereHas('categories', function ($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            }

            if ($excludeBookId) {
                $allBooks->where('books.id', '!=', $excludeBookId);
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

        $query
            ->selectRaw('books.*, COALESCE(CAST(AVG(transaction_items.rating) AS FLOAT), 0) as average_rating')
            ->selectRaw('COALESCE(CAST(SUM(CASE WHEN transaction_items.rating > 0 THEN 1 ELSE 0 END) AS FLOAT), 0) as rating_count')
            ->selectRaw('COALESCE(SUM(transaction_items.quantity), 0) as sold')
            ->leftJoin('transaction_items', 'books.id', '=', 'transaction_items.book_id')
            ->groupBy('books.id')
            ->orderBy('sold', 'desc');

        return $query->paginate($limit);
    }

    public static function getBooks(
        $search = null,
        $limit = 10,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
        $categorySlug = null
    ) {
        $query = Book::query()
            ->selectRaw('books.*, COALESCE(CAST(AVG(transaction_items.rating) AS FLOAT), 0) as average_rating')
            ->selectRaw('COALESCE(CAST(SUM(CASE WHEN transaction_items.rating > 0 THEN 1 ELSE 0 END) AS FLOAT), 0) as rating_count')
            ->selectRaw('COALESCE(SUM(transaction_items.quantity), 0) as sold')
            ->leftJoin('transaction_items', 'books.id', '=', 'transaction_items.book_id')
            ->groupBy('books.id');

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
        return Book::query()
            ->selectRaw('books.*, COALESCE(CAST(AVG(transaction_items.rating) AS FLOAT), 0) as average_rating')
            ->selectRaw('COALESCE(CAST(SUM(CASE WHEN transaction_items.rating > 0 THEN 1 ELSE 0 END) AS FLOAT), 0) as rating_count')
            ->selectRaw('COALESCE(SUM(transaction_items.quantity), 0) as sold')
            ->leftJoin('transaction_items', 'books.id', '=', 'transaction_items.book_id')
            ->groupBy('books.id')
            ->where('slug', $slug)->first();
    }

    public static function addBookReview(
        $transactionItem,
        $rating,
        $review,
        $attachments = []
    ) {
        $transactionItem->rating = $rating;
        $transactionItem->review = $review;
        $transactionItem->reviewed_at = now();
        $transactionItem->save();

        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $filePath = Storage::disk('public')->put('reviews', $attachment);

                $transactionItem->attachments()->create([
                    'file_path' => $filePath,
                    'file_type' => str_starts_with($attachment->getMimeType(), 'image/') ?
                        ReviewAttachmentFileType::IMAGE->value :
                        ReviewAttachmentFileType::VIDEO->value,
                    'caption' => null,
                ]);
            }
        }

        $transactionItem->load('attachments');

        return $transactionItem;
    }

    public static function getBookReviews($book)
    {
        return TransactionItem::where('book_id', $book->id)
            ->select(
                'transaction_items.id',
                'transaction_items.user_id',
                'transaction_items.rating',
                'transaction_items.review',
                'transaction_items.reviewed_at',
                'transaction_items.created_at'
            )
            ->with(['user'])
            ->whereNotNull('rating')
            ->where('rating', '>', 0)
            ->orderBy('created_at', 'desc')
            ->get()
            ->makeHidden(['book', 'image']);
    }

    public static function createBook($data)
    {
        $existingBook = Book::where('slug', $data['slug'])->first();

        if (!$existingBook) {
            $book = Book::create($data);
        } else {
            $book = $existingBook;
        }

        if (isset($data['image'])) {
            // Save image
            $book->image = $data['image']->store('book', 'public');
            $book->save();
        }

        if (!empty($data['category_slug'])) {
            $category = CategoryRepository::findCategoryBySlug($data['category_slug']);
            if ($category) {
                $book->categories()->syncWithoutDetaching([$category->id]);
            }
        }

        if (!empty($data['categories'])) {
            $book->categories()->syncWithoutDetaching($data['categories']);
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

        if (isset($data['image'])) {
            // Save new image
            $book->image = $data['image']->store('book', 'public');
            $book->save();
        }

        if (array_key_exists('categories', $data)) {
            if (!empty($data['categories'])) {
                $book->categories()->sync($data['categories']);
            } else {
                $book->categories()->detach();
            }
        }

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
