<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\TransactionItem;
use App\Repositories\BookRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    public function userRecommendedBooks(Request $request)
    {
        $user = $request->user();
        $limit = $request->input('limit', 10);

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

    public function addBookReview(Request $request, $transactionItemId)
    {
        $transactionItem = TransactionItem::findOrFail($transactionItemId);
        $user = $request->user();

        if ($transactionItem->user_id !== $user->id) {
            return ResponseFormatter::error('Unauthorized to add review for this transaction item.', 403);
        }

        $validatedData = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'image|mimes:jpeg,png,jpg,gif,svg,mp4,mov,avi|max:20480',
        ]);

        try {
            $rating = $validatedData['rating'];
            $review = $validatedData['review'] ?? null;
            $attachments = $validatedData['attachments'] ?? [];

            $transactionItem = BookRepository::addBookReview(
                transactionItem: $transactionItem,
                rating: $rating,
                review: $review,
                attachments: $attachments
            );

            return ResponseFormatter::success($transactionItem, 'Book review added successfully.');
        } catch (Exception $e) {
            Log::error('Error in addBookReview controller: ' . $e->getMessage());
            return ResponseFormatter::error('Failed to add book review.' . $e, 500);
        }
    }
}
