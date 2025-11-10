<?php

namespace App\Repositories;

use App\Models\Category;
use Exception;
use Illuminate\Support\Facades\DB;

class CategoryRepository
{
    static public function getCategories(
        $search = null,
        $limit = 10,
        $orderBy = 'created_at',
        $orderDirection = 'desc'
    ) {
        $query = Category::query();

        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        $query->orderBy($orderBy, $orderDirection);

        return $query->paginate($limit);
    }

    static public function findCategoryBySlug($slug)
    {
        return Category::where('slug', $slug)->first();
    }

    static public function createCategory($data)
    {
        try {
            DB::beginTransaction();

            $category = Category::create([
                'title' => $data['title'],
                'slug' => $data['slug'] ?? null,
                'image' => $data['image'] ?? null,
            ]);

            if (isset($data['subcategories']) && is_array($data['subcategories'])) {
                foreach ($data['subcategories'] as $subcategoryData) {
                    $category->subcategories()->create([
                        'title' => $subcategoryData['title'],
                        'slug' => $subcategoryData['slug'] ?? null,
                        'image' => $subcategoryData['image'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return $category;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    static public function insertCategories($categories)
    {
        return Category::insert($categories);
    }

    static public function updateCategory($category, $data)
    {
        $category->update($data);
        return $category;
    }

    static public function deleteCategory($category)
    {
        return $category->delete();
    }
}
