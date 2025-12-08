<?php

namespace App\UseCases;

use App\Models\Book;
use App\Models\ProductVariant;
use App\Models\Store;

class SyncCartUseCase
{
    public static function sync($group)
    {
        $store = Store::find($group['store_id']);
        $group['store'] = $store;
        $group['updated_at'] = now()->toDateTimeString();

        $items = $group['items'];
        $updateItems = [];

        foreach ($items as $item) {
            $book = Book::find($item['book_id']);
            if (!$book) continue;

            $updatedItem = $item;

            // Add book and image to the item
            $updatedItem['book'] = $book;
            $updatedItem['image'] = $book->image;

            // Add created_at and updated_at timestamps
            $updatedItem['updated_at'] = $item['updated_at'] ?? now()->toDateTimeString();

            $updateItems[] = $updatedItem;
        }

        $group['items'] = $updateItems;
        return $group;
    }
}
