<?php

namespace App\Http\Controllers\Admin;

use App\Exports\BooksExport;
use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Repositories\BookRepository;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $limit = $request->input('limit', 10);
        $orderBy = $request->input('orderBy', 'created_at');
        $orderDirection = $request->input('orderDirection', 'desc');
        $page = $request->input('page', 1);
        $categorySlug = $request->input('category_slug');

        // $books = BookRepository::getBooks(
        //     search: $search,
        //     limit: $limit,
        //     orderBy: $orderBy,
        //     orderDirection: $orderDirection,
        //     categorySlug: $categorySlug
        // );

        $books = BookRepository::getRecommendedBooks(
            search: $search,
            limit: $limit,
            page: $page,
            categorySlug: $categorySlug
        );

        return Inertia::render('Admin/Book/Index', [
            'books' => $books,
            'categories' => Category::where('parent_id', null)->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Book/CreateBook');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'translated_title' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:books,slug',
            'author' => 'nullable|string|max:255',
            'store_name' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:255',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        BookRepository::createBook($data);

        return redirect()->route('admin.books.index')->with('success', 'Book created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        return Inertia::render('Admin/Book/Show', [
            'book' => $book,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        $book->load('categories');
        $categories = CategoryRepository::getCategoryDropdown();

        return Inertia::render('Admin/Book/Edit', [
            'book' => $book,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'translated_title' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:books,slug,' . $book->id,
            'author' => 'nullable|string|max:255',
            'store_name' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:255',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        BookRepository::updateBook($book, $data);

        return redirect()->route('admin.book.index')->with('success', 'Book updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        BookRepository::deleteBook($book);

        return redirect()->route('admin.book.index')->with('success', 'Book deleted successfully.');
    }

    public function scraping()
    {
        $books = BookRepository::getBooks();
        return Inertia::render('Admin/Book/Scraping', [
            'books' => $books,
        ]);
    }

    public function preprocessing()
    {
        $books = BookRepository::getBooks();

        return Inertia::render('Admin/Book/Preprocessing', [
            'books' => $books,
        ]);
    }

    public function export()
    {
        return Excel::download(new BooksExport, 'books.xlsx');
    }
}
