<?php

namespace App\Repositories;

use App\Models\Category;
use Exception;
use Illuminate\Support\Facades\DB;

class CategoryRepository
{
    public static function getCategories(
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

    public static function getCategoryDropdown()
    {
        return Category::orderBy('title', 'asc')->whereNull('parent_id')->get(['id', 'title']);
    }

    public static function findCategoryBySlug($slug)
    {
        return Category::where('slug', $slug)->first();
    }

    public static function createCategory($data, $parentId = null)
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

    public static function insertCategories($categories)
    {
        foreach ($categories as $categoryData) {
            self::createCategory($categoryData);
        }
    }

    public static function updateCategory($category, $data)
    {
        $category->update($data);
        return $category;
    }

    public static function deleteCategory($category)
    {
        return $category->delete();
    }
}
