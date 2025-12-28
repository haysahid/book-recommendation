<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Repositories\CategoryRepository;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Categories
        $json = file_get_contents(database_path('seeders/source/scraped_categories.json'));
        $categories = json_decode($json, true);

        CategoryRepository::insertCategories($categories);

        // Books
        $json = file_get_contents(database_path('seeders/source/scraped_books.json'));
        $books = json_decode($json, true);

        foreach ($books as $book) {
            $existingBook = Book::where('slug', $book['slug'])->first();
            $categorySlug = !empty($book['category_slug']) ? $book['category_slug'] : null;

            if (!$existingBook) {
                $book = Book::create([
                    'title' => !empty($book['title']) ? $book['title'] : null,
                    'translated_title' => !empty($book['translated_title']) ? $book['translated_title'] : null,
                    'image' => !empty($book['image']) ? $book['image'] : null,
                    'slug' => !empty($book['slug']) ? $book['slug'] : null,
                    'author' => !empty($book['author']) ? $book['author'] : null,
                    'final_price' => !empty($book['final_price']) ? $book['final_price'] : null,
                    'slice_price' => !empty($book['slice_price']) ? $book['slice_price'] : null,
                    'discount' => !empty($book['discount']) ? $book['discount'] : 0,
                    'is_oos' => isset($book['is_oos']) ? $book['is_oos'] : null,
                    'sku' => !empty($book['sku']) ? $book['sku'] : null,
                    'format' => !empty($book['format']) ? $book['format'] : null,
                    'store_name' => !empty($book['store_name']) ? $book['store_name'] : null,
                    'isbn' => !empty($book['isbn']) ? $book['isbn'] : null,
                ]);
            } else {
                $book = $existingBook;
            }

            if ($categorySlug) {
                $category = CategoryRepository::findCategoryBySlug($categorySlug);
                if ($category) {
                    $book->categories()->syncWithoutDetaching([$category->id]);
                }
            }
        }
    }
}
