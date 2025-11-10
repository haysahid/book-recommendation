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

    static public function createCategory($data, $parentId = null)
    {
        try {
            DB::beginTransaction();

            $category = Category::where('slug', $data['slug'] ?? '')->first();

            if (!$category) {
                $category = new Category();
                $category->title = $data['title'];
                $category->slug = $data['slug'] ?? null;
                $category->image = $data['image'] ?? null;
                $category->parent_id = $parentId;
                $category->save();
            }

            if (isset($data['subcategory']) && is_array($data['subcategory'])) {
                foreach ($data['subcategory'] as $subcategoryData) {
                    self::createCategory($subcategoryData, $category->id);
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
        foreach ($categories as $categoryData) {
            self::createCategory($categoryData);
        }
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
