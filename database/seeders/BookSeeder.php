<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = file_get_contents(database_path('seeders/source/gramedia_books.json'));
        $books = json_decode($json, true);

        foreach ($books as $book) {
            Book::create([
            'title' => !empty($book['title']) ? $book['title'] : null,
            'translated_title' => !empty($book['translated_title']) ? $book['translated_title'] : null,
            'image' => !empty($book['image']) ? $book['image'] : null,
            'slug' => !empty($book['slug']) ? $book['slug'] : null,
            'author' => !empty($book['author']) ? $book['author'] : null,
            'store_name' => !empty($book['store_name']) ? $book['store_name'] : null,
            'isbn' => !empty($book['isbn']) ? $book['isbn'] : null,
            ]);
        }
    }
}
