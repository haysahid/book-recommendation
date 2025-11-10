<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import useBookService from "@/services/book-service";
import { ref } from "vue";
import ThreeDotsLoading from "@/Components/ThreeDotsLoading.vue";
import SummaryCard from "@/Components/SummaryCard.vue";
import CheckBox from "@/Components/CheckBox.vue";

const categories = ref<CategoryModel[]>([]);
const getCategoriesStatus = ref<string>("");
const saveCategoriesStatus = ref<string>("");

const selectedCategories = ref<CategoryModel[]>([]);

const books = ref<BookEntity[]>([]);
const loadBooksStatus = ref<string>("");

const bookService = useBookService();

const loadBookCurrentCategory = ref<CategoryModel | null>(null);
const loadBookCurrentPage = ref(0);
const loadBookPage = ref(1);
const loadBookLimit = 28;

const totalBooksLoaded = ref(0);

function loadBooksByCategory(category: CategoryModel) {
    if (
        loadBookCurrentCategory.value!.count_loaded_books! >=
        loadBookCurrentCategory.value!.total_data_books!
    ) {
        loadBooksStatus.value = "success";
        return;
    }

    // Load books page by page with delay 3 - 5 seconds
    loadBooksStatus.value = "loading";
    bookService.loadBooks(
        {
            page: loadBookPage.value,
            limit: loadBookLimit,
            category: category,
        },
        {
            onSuccess: (response, totalData, isFromCache) => {
                // Book just to preview per page
                books.value = response;

                // Update selected category
                loadBookCurrentCategory.value = {
                    ...loadBookCurrentCategory.value,
                    count_loaded_books:
                        (loadBookCurrentCategory.value?.count_loaded_books ||
                            0) + response.length,
                    total_data_books: totalData,
                };

                selectedCategories.value = selectedCategories.value.map(
                    (cat) => {
                        if (cat.slug === loadBookCurrentCategory.value.slug) {
                            return loadBookCurrentCategory.value;
                        }
                        return cat;
                    }
                );

                totalBooksLoaded.value += response.length;

                if (
                    loadBookCurrentCategory.value!.count_loaded_books! <
                    loadBookCurrentCategory.value!.total_data_books!
                ) {
                    loadBookCurrentPage.value = loadBookPage.value;
                    loadBookPage.value += 1;

                    if (!isFromCache) {
                        // Delay 3 - 5 seconds
                        const delay = Math.floor(Math.random() * 2000) + 3000;
                        setTimeout(() => {
                            loadBooksByCategory(category);
                        }, delay);
                    } else {
                        setTimeout(() => {
                            loadBooksByCategory(category);
                        }, 200);
                    }
                } else {
                    loadBooksStatus.value = "success";
                }
            },
            onChangeStatus: (status) => {
                // getBooksStatus.value = status;
            },
        }
    );
}

function loadBooks() {
    // For each selected category, load books
    if (selectedCategories.value.length === 0) {
        alert("Please select at least one category");
        return;
    }

    // Reset
    for (const category of selectedCategories.value) {
        loadBookCurrentCategory.value = category;
        books.value = [];
        loadBookCurrentPage.value = 0;
        loadBookPage.value = 1;
        loadBooksByCategory(category);
    }
}

const saveBooksStatus = ref<string>("");

function saveBooksByCategory(category: CategoryModel) {
    saveBooksStatus.value = "loading";
    bookService.saveBooks(category, {
        onSuccess: (response) => {
            saveBooksStatus.value = "success";
        },
    });
}

function saveBooks() {
    // For each selected category, save books
    if (selectedCategories.value.length === 0) {
        alert("Please select at least one category");
        return;
    }

    for (const category of selectedCategories.value) {
        if (category.count_loaded_books && category.count_loaded_books > 0) {
            saveBooksByCategory(category);
        }
    }
}
</script>

<template>
    <AdminLayout title="Scraping Data" :showTitle="true">
        <div class="p-4">
            <h1 class="text-2xl font-bold mb-1">Scraping Data</h1>

            <p class="text-base mb-6 text-gray-600">Source: Gramedia</p>

            <!-- Get Categories -->
            <DefaultCard class="mb-6">
                <div
                    class="flex flex-col sm:flex-row gap-2.5 sm:items-start sm:justify-between mb-4"
                >
                    <div>
                        <h3 class="text-lg font-semibold">Categories</h3>
                        <div class="text-sm text-blue-500 mt-1 font-semibold">
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
                            v-if="categories.length > 0"
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
                    class="list-none list-inside grid gap-2 md:grid-cols-2 lg:grid-cols-3"
                >
                    <li v-for="category in categories" :key="category.slug">
                        <label
                            :for="`category_${category.slug}`"
                            class="text-gray-700 hover:text-gray-900 cursor-pointer flex items-center"
                        >
                            <CheckBox
                                :id="`category_${category.slug}`"
                                :checked="
                                    selectedCategories.some(
                                        (cat) => cat.slug === category.slug
                                    )
                                "
                                :value="category.title"
                                :label="category.title"
                                class="mr-2"
                                @update:checked="
                                    (isChecked) => {
                                        if (isChecked) {
                                            selectedCategories.push(category);
                                        } else {
                                            selectedCategories =
                                                selectedCategories.filter(
                                                    (cat) =>
                                                        cat.slug !==
                                                        category.slug
                                                );
                                        }
                                    }
                                "
                            />
                            <span>{{ category.title }}</span>
                        </label>
                    </li>
                </ul>
            </DefaultCard>

            <!-- Get Books -->
            <DefaultCard class="mb-6">
                <div
                    class="flex flex-col sm:flex-row gap-2.5 sm:items-start sm:justify-between mb-4"
                >
                    <div>
                        <h3 class="text-lg font-semibold">Books</h3>
                        <!-- <div class="text-sm text-blue-500 mt-2 font-semibold">
                            {{ books.length }} Loaded
                        </div> -->
                    </div>
                    <div class="flex gap-2 items-center">
                        <PrimaryButton @click="loadBooks()">
                            Load All Books
                        </PrimaryButton>
                        <PrimaryButton
                            v-if="
                                selectedCategories.reduce(
                                    (total, category) =>
                                        total +
                                        (category.count_loaded_books || 0),
                                    0
                                ) > 0
                            "
                            class="bg-green-600! hover:bg-green-600/80! focus:ring-green-500!"
                            @click="saveBooks()"
                        >
                            Save All Books
                        </PrimaryButton>
                    </div>
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4"
                >
                    <SummaryCard
                        title="Total Books to Load"
                        :value="
                            selectedCategories.reduce(
                                (total, category) =>
                                    total + (category.total_data_books || 0),
                                0
                            )
                        "
                    />
                    <SummaryCard
                        title="Books Loaded"
                        :value="totalBooksLoaded"
                    />
                    <SummaryCard
                        title="Books Remaining"
                        :value="
                            selectedCategories.reduce(
                                (total, category) =>
                                    total +
                                    ((category.total_data_books || 0) -
                                        (category.count_loaded_books || 0)),
                                0
                            )
                        "
                    />
                </div>

                <div
                    v-if="loadBooksStatus === 'loading'"
                    class="my-8 flex flex-col items-center"
                >
                    <p class="text-blue-500">
                        Loading books... (Page {{ loadBookPage }})
                    </p>
                    <ThreeDotsLoading class="text-blue-500" />
                </div>

                <div v-if="selectedCategories.length > 0" class="mt-4">
                    <h4 class="mb-2">Loaded Books</h4>
                    <!-- Selected Categories -->
                    <div class="flex flex-col gap-2">
                        <DefaultCard
                            v-for="category in selectedCategories"
                            :key="category.slug"
                            class="p-4"
                        >
                            <div class="flex gap-2 items-center">
                                <div class="w-full">
                                    <h5 class="font-semibold mb-2">
                                        {{ category.title }}
                                    </h5>
                                    <p class="text-blue-500 font-semibold">
                                        {{ category.count_loaded_books || 0 }} /
                                        {{ category.total_data_books || "-" }}
                                    </p>
                                </div>

                                <ThreeDotsLoading
                                    v-if="
                                        loadBooksStatus === 'loading' &&
                                        loadBookCurrentCategory &&
                                        loadBookCurrentCategory.slug ===
                                            category.slug
                                    "
                                    class="text-blue-500"
                                />

                                <PrimaryButton
                                    v-else-if="
                                        !category.count_loaded_books ||
                                        (category.count_loaded_books || 0) <
                                            (category.total_data_books || 0)
                                    "
                                    @click="
                                        loadBookCurrentCategory = category;
                                        books = [];
                                        loadBookCurrentPage = 0;
                                        loadBookPage = 1;
                                        loadBooksByCategory(category);
                                    "
                                    class="whitespace-nowrap h-fit"
                                >
                                    Load Books
                                </PrimaryButton>

                                <!-- Save -->
                                <PrimaryButton
                                    v-else-if="category.count_loaded_books"
                                    class="bg-green-600! hover:bg-green-600/80! focus:ring-green-500! whitespace-nowrap h-fit"
                                    @click="
                                        bookService.saveBooks(category, {
                                            onChangeStatus: (status) => {
                                                saveBooksStatus = status;
                                            },
                                        })
                                    "
                                >
                                    Save Books
                                </PrimaryButton>
                            </div>
                        </DefaultCard>
                    </div>
                </div>
            </DefaultCard>
        </div>
    </AdminLayout>
</template>
