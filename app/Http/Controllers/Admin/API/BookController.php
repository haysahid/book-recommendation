<?php

namespace App\Http\Controllers\Admin\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Repositories\BookRepository;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function storeBulk(Request $request)
    {
        $data = $request->validate([
            'books' => 'required|array',
            'books.*.title' => 'required|string|max:255',
            'books.*.image' => 'nullable|string|max:255',
            'books.*.slug' => 'required|string|max:255|unique:books,slug',
            'books.*.author' => 'nullable|string|max:255',
            'books.*.store_name' => 'nullable|string|max:255',
            'books.*.isbn' => 'nullable|string|max:255',
            'books.*.category_slug' => 'nullable|string|max:255',
        ]);

        try {
            BookRepository::insertBooks($data['books']);
            return ResponseFormatter::success(null, 'Books created successfully.');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to create books.', 500);
        }
    }
}
