<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
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
}
