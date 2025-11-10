<?php

namespace App\Http\Controllers\Admin\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function storeBulk(Request $request)
    {
        $data = $request->validate([
            'categories' => 'required|array',
            'categories.*.title' => 'required|string|max:255',
            'categories.*.slug' => 'nullable|string|max:255|unique:categories,slug',
        ]);

        try {
            CategoryRepository::insertCategories($data['categories']);
            return ResponseFormatter::success(null, 'Categories created successfully.');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to create categories.', 500);
        }
    }
}
