<script setup lang="ts">
const props = defineProps({
    books: {
        type: Object as () => PaginationModel<BookEntity>,
        required: true,
    },
});
</script>

<template>
    <div class="p-4">
        <h1 class="text-2xl font-bold mb-4">Admin Book Index Page</h1>
        <!-- Content for the Admin Book Index Page goes here -->
        <div
            v-for="book in props.books.data"
            :key="book.id"
            class="mb-2 p-4 border rounded"
        >
            <h2 class="text-xl font-semibold">{{ book.title }}</h2>
            <p class="text-gray-600">Author: {{ book.author }}</p>
            <p class="text-gray-600">Store: {{ book.store_name }}</p>
            <p class="text-gray-600">ISBN: {{ book.isbn }}</p>
        </div>
    </div>
</template>
