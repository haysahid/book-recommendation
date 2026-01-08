<?php

namespace App\Http\Controllers\Admin\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\User;
use App\Repositories\BookRepository;
use App\UseCases\AutoTrainModelUseCase;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    private AutoTrainModelUseCase $autoTrainModelUseCase;

    public function __construct()
    {
        $this->autoTrainModelUseCase = new AutoTrainModelUseCase();
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $limit = $request->input('limit', 10);
        $orderBy = $request->input('orderBy', 'created_at');
        $orderDirection = $request->input('orderDirection', 'desc');

        $books = BookRepository::getBooks($search, $limit, $orderBy, $orderDirection);

        return ResponseFormatter::success($books, 'Books retrieved successfully.');
    }

    public function storeBulk(Request $request)
    {
        $data = $request->validate([
            'books' => 'required|array',
            'books.*.title' => 'required|string|max:255',
            'books.*.image' => 'nullable|string|max:255',
            'books.*.slug' => 'required|string|max:255',
            'books.*.author' => 'nullable|string|max:255',
            'books.*.store_name' => 'nullable|string|max:255',
            'books.*.isbn' => 'nullable|string|max:255',
            'books.*.category_slug' => 'nullable|string|max:255',
        ]);

        try {
            BookRepository::insertBooks($data['books']);

            $this->autoTrainModelUseCase->execute();

            return ResponseFormatter::success(null, 'Books created successfully.');
        } catch (Exception $e) {
            return ResponseFormatter::error('Failed to create books.' . $e, 500);
        }
    }

    public function cleanTitleBulk(Request $request)
    {
        $data = $request->validate([
            'book_ids' => 'nullable|array',
        ]);

        try {
            $books = isset($data['book_ids']) ? Book::whereIn('id', $data['book_ids'])->get() : Book::all();
            $cleanedTitles = BookRepository::cleanTitles($books);
            return ResponseFormatter::success($cleanedTitles, 'Titles cleaned successfully.');
        } catch (Exception $e) {
            return ResponseFormatter::error('Failed to clean titles.' . $e, 500);
        }
    }

    public function stemTitleBulk(Request $request)
    {
        $data = $request->validate([
            'book_ids' => 'nullable|array',
        ]);

        try {
            $books = isset($data['book_ids']) ? Book::whereIn('id', $data['book_ids'])->get() : Book::all();
            $stemmedTitles = BookRepository::stemTitlesWithSastrawi($books);
            return ResponseFormatter::success($stemmedTitles, 'Titles stemmed successfully.');
        } catch (Exception $e) {
            return ResponseFormatter::error('Failed to stem titles.', 500);
        }
    }

    public function userRecommendedBooks(Request $request, $userId)
    {
        $user = User::find($userId);
        $limit = $request->input('limit', 10);

        if (!$user) {
            return ResponseFormatter::error('User not found.', 404);
        }

        try {
            $result = BookRepository::getUserRecommendedBooks(
                user: $user,
                limit: $limit
            );
            return ResponseFormatter::success($result, 'User recommended books retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Error in userRecommendedBooks controller: ' . $e->getMessage());
            return ResponseFormatter::error('Failed to retrieve user recommended books.', 500);
        }
    }
}
