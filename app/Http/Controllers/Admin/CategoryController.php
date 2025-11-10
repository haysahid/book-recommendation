<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
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

        $categories = CategoryRepository::getCategories($search, $limit, $orderBy, $orderDirection);

        return Inertia::render('Admin/Category/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Category/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'image' => 'nullable|string|max:255',
            'subcategories' => 'nullable|array',
        ]);

        CategoryRepository::createCategory($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function storeBulk(Request $request)
    {
        $data = $request->validate([
            'categories' => 'required|array',
            'categories.*.title' => 'required|string|max:255',
            'categories.*.slug' => 'nullable|string|max:255|unique:categories,slug',
            'categories.*.image' => 'nullable|string|max:255',
        ]);

        CategoryRepository::insertCategories($data['categories']);

        return redirect()->route('admin.categories.index')->with('success', 'Categories created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return Inertia::render('Admin/Category/Show', [
            'category' => $category,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return Inertia::render('Admin/Category/Edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'image' => 'nullable|string|max:255',
            'subcategories' => 'nullable|array',
        ]);

        CategoryRepository::updateCategory($category, $data);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        CategoryRepository::deleteCategory($category);

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}
