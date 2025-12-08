<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Repositories\BookRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        $books = BookRepository::getRecommendedBooks(
            search: $search,
            limit: $limit,
            page: $page,
        );

        return Inertia::render('Book/Index', [
            'books' => $books,
        ]);
    }

    public function show(string $bookSlug)
    {
        $book = BookRepository::findBookBySlug($bookSlug);

        if (!$book) {
            abort(404);
        }

        $relatedBooks = BookRepository::getRelatedBooks(
            book: $book,
            limit: 8,
        );

        return Inertia::render('Book/Show', [
            'book' => $book,
            'relatedBooks' => $relatedBooks,
        ]);
    }

    public function favorite()
    {
        return Inertia::render('Favorite/Index');
    }
}
