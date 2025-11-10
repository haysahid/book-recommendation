<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import useBookService from "@/services/book-service";
import { ref } from "vue";
import ThreeDotsLoading from "@/Components/ThreeDotsLoading.vue";

const props = defineProps({
    books: {
        type: Object as () => PaginationModel<BookEntity>,
        required: true,
    },
});

const categories = ref<CategoryModel[]>([]);
const getCategoriesStatus = ref<string>("");
const saveCategoriesStatus = ref<string>("");

const selectedCategories = ref<CategoryModel[]>([]);

const books = ref<BookEntity[]>([]);
const getBooksStatus = ref<string>("");
const saveBooksStatus = ref<string>("");

const bookService = useBookService();
</script>

<template>
    <AdminLayout title="Books" :showTitle="true">
        <div class="p-4">
            <h1 class="text-2xl font-bold mb-4">Sistem Rekomendasi Buku</h1>

            <h2 class="text-xl font-bold mb-4">Scraping Data</h2>

            <!-- Get Categories -->
            <DefaultCard class="mb-6">
                <div class="flex gap-4 items-center justify-between mb-1.5">
                    <div class="mb-2">
                        <h3 class="text-lg font-semibold">Categories</h3>
                        <a
                            href="https://api-service.gramedia.com/api/v2/public/subcategory?parent_category=buku"
                            class="text-sm text-gray-500 hover:underline hover:text-gray-700"
                        >
                            https://api-service.gramedia.com/api/v2/public/subcategory?parent_category=buku
                        </a>
                        <div class="text-sm text-gray-500">
                            {{ categories.length }} Loaded
                        </div>
                    </div>
                    <div class="flex gap-2 items-center">
                        <PrimaryButton
                            @click="
                                bookService.loadCategories(
                                    {},
                                    {
                                        onSuccess: (response) => {
                                            categories = response.data.data;
                                        },
                                        onChangeStatus: (status) => {
                                            getCategoriesStatus = status;
                                        },
                                    }
                                )
                            "
                        >
                            Load Categories
                        </PrimaryButton>
                        <PrimaryButton
                            class="bg-green-600! hover:bg-green-600/80! focus:ring-green-500!"
                            @click="
                                bookService.saveCategories(categories, {
                                    onChangeStatus: (status) => {
                                        saveCategoriesStatus = status;
                                    },
                                })
                            "
                        >
                            Save Categories
                        </PrimaryButton>
                    </div>
                </div>

                <ThreeDotsLoading
                    v-if="getCategoriesStatus === 'loading'"
                    class="my-8 text-blue-500"
                />

                <ul
                    class="list-disc list-inside grid gap-2 md:grid-cols-2 lg:grid-cols-3"
                >
                    <li
                        v-for="category in categories"
                        :key="category.slug"
                        class="text-gray-700"
                    >
                        {{ category.title }}
                    </li>
                </ul>
            </DefaultCard>

            <!-- Get Books -->
            <DefaultCard class="mb-6">
                <div class="flex gap-4 items-center justify-between mb-1.5">
                    <div class="mb-2">
                        <h3 class="text-lg font-semibold mb-2">Books</h3>
                        <div class="text-sm text-gray-500">
                            Total Books Loaded: {{ books.length }}
                        </div>
                    </div>
                    <PrimaryButton
                        @click="
                            bookService.getBooks(
                                {},
                                {
                                    onSuccess: (response) => {
                                        books = response.data.data;
                                    },
                                }
                            )
                        "
                        >Load Books</PrimaryButton
                    >
                </div>

                <ul
                    class="list-disc list-inside grid gap-2 md:grid-cols-2 lg:grid-cols-3"
                >
                    <li
                        v-for="book in books"
                        :key="book.id"
                        class="text-gray-700"
                    >
                        {{ book.title }}
                    </li>
                </ul>
            </DefaultCard>
        </div>
    </AdminLayout>
</template>
