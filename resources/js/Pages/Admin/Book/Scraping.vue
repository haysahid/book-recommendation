<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import ThreeDotsLoading from "@/Components/ThreeDotsLoading.vue";
import SummaryCard from "@/Components/SummaryCard.vue";
import CheckBox from "@/Components/CheckBox.vue";
import { useScrapingStore } from "@/stores/scraping-store";

const scrapingStore = useScrapingStore();
</script>

<template>
    <AdminLayout title="Scraping Data" :showTitle="true">
        <div class="p-4">
            <div class="flex gap-4 items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-1">Scraping Data</h1>
                    <p class="text-base text-gray-600">Source: Gramedia</p>
                </div>
                <SecondaryButton @click="scrapingStore.clearStore()">
                    Clear
                </SecondaryButton>
            </div>

            <!-- Get Categories -->
            <DefaultCard class="mb-6">
                <div
                    class="flex flex-col sm:flex-row gap-2.5 sm:items-start sm:justify-between mb-4"
                >
                    <div>
                        <h3 class="text-lg font-semibold">Categories</h3>
                        <div class="text-sm text-blue-500 mt-1 font-semibold">
                            {{ scrapingStore.categories.length }} Loaded
                        </div>
                    </div>
                    <div class="flex gap-2 items-center">
                        <PrimaryButton
                            @click="
                                scrapingStore.bookService.loadCategories(
                                    {},
                                    {
                                        onSuccess: (response) => {
                                            scrapingStore.categories =
                                                response.data.data;
                                        },
                                        onChangeStatus: (status) => {
                                            scrapingStore.getCategoriesStatus =
                                                status;
                                        },
                                    }
                                )
                            "
                        >
                            Load Categories
                        </PrimaryButton>
                        <PrimaryButton
                            v-if="scrapingStore.categories.length > 0"
                            class="bg-green-600! hover:bg-green-600/80! focus:ring-green-500!"
                            @click="
                                scrapingStore.bookService.saveCategories(
                                    scrapingStore.categories,
                                    {
                                        onChangeStatus: (status) => {
                                            scrapingStore.saveCategoriesStatus =
                                                status;
                                        },
                                    }
                                )
                            "
                        >
                            Save Categories
                        </PrimaryButton>
                    </div>
                </div>

                <ThreeDotsLoading
                    v-if="scrapingStore.getCategoriesStatus === 'loading'"
                    class="my-8 text-blue-500"
                />

                <!-- Select All -->
                <label
                    v-if="scrapingStore.categories.length > 0"
                    for="select_all_categories"
                    class="flex items-center mb-2 cursor-pointer"
                >
                    <CheckBox
                        id="select_all_categories"
                        :checked="
                            scrapingStore.selectedCategories.length ===
                                scrapingStore.categories.length &&
                            scrapingStore.categories.length > 0
                        "
                        :label="'Select All'"
                        class="mr-2"
                        @update:checked="
                            (isChecked) => {
                                if (isChecked) {
                                    scrapingStore.selectedCategories = [
                                        ...scrapingStore.categories,
                                    ];
                                } else {
                                    scrapingStore.selectedCategories = [];
                                }
                            }
                        "
                    />
                    <span class="text-gray-700">Select All</span>
                </label>

                <ul
                    class="list-none list-inside grid gap-2 md:grid-cols-2 lg:grid-cols-3"
                >
                    <li
                        v-for="category in scrapingStore.categories"
                        :key="category.slug"
                    >
                        <label
                            :for="`category_${category.slug}`"
                            class="text-gray-700 hover:text-gray-900 cursor-pointer flex items-center"
                        >
                            <CheckBox
                                :id="`category_${category.slug}`"
                                :checked="
                                    scrapingStore.selectedCategories.some(
                                        (cat) => cat.slug === category.slug
                                    )
                                "
                                :value="category.title"
                                :label="category.title"
                                class="mr-2"
                                @update:checked="
                                    (isChecked) => {
                                        if (isChecked) {
                                            scrapingStore.selectedCategories.push(
                                                category
                                            );
                                        } else {
                                            scrapingStore.selectedCategories =
                                                scrapingStore.selectedCategories.filter(
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
                    </div>
                    <div class="flex gap-2 items-center">
                        <PrimaryButton @click="scrapingStore.loadBooks()">
                            Load All Books
                        </PrimaryButton>
                        <PrimaryButton
                            v-if="
                                scrapingStore.selectedCategories.reduce(
                                    (total, category) =>
                                        total +
                                        (category.count_loaded_books || 0),
                                    0
                                ) > 0
                            "
                            class="bg-green-600! hover:bg-green-600/80! focus:ring-green-500!"
                            @click="scrapingStore.saveBooks()"
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
                            scrapingStore.selectedCategories.reduce(
                                (total, category) =>
                                    total + (category.total_data_books || 0),
                                0
                            )
                        "
                    />
                    <SummaryCard
                        title="Books Loaded"
                        :value="scrapingStore.totalBooksLoaded"
                    />
                    <SummaryCard
                        title="Books Remaining"
                        :value="
                            scrapingStore.selectedCategories.reduce(
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
                    v-if="scrapingStore.loadBooksStatus === 'loading'"
                    class="my-8 flex flex-col items-center"
                >
                    <p class="text-blue-500">
                        Loading books of
                        {{ scrapingStore.loadBookCurrentCategory.title }}...
                        (Page {{ scrapingStore.loadBookPage }})
                    </p>
                    <ThreeDotsLoading class="text-blue-500" />
                </div>

                <div
                    v-if="scrapingStore.selectedCategories.length > 0"
                    class="mt-4"
                >
                    <h4 class="mb-2">Loaded Books</h4>
                    <!-- Selected Categories -->
                    <div class="flex flex-col gap-2">
                        <DefaultCard
                            v-for="category in scrapingStore.selectedCategories"
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
                                        scrapingStore.loadBooksStatus ===
                                            'loading' &&
                                        scrapingStore.loadBookCurrentCategory &&
                                        scrapingStore.loadBookCurrentCategory
                                            .slug === category.slug
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
                                        scrapingStore.loadBookCurrentCategory =
                                            category;
                                        scrapingStore.books = [];
                                        scrapingStore.loadBookCurrentPage = 0;
                                        scrapingStore.loadBookPage = 1;
                                        scrapingStore.loadBooksByCategory(
                                            category
                                        );
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
                                        scrapingStore.bookService.saveBooks(
                                            category,
                                            {
                                                onChangeStatus: (status) => {
                                                    scrapingStore.saveBooksStatus =
                                                        status;
                                                },
                                            }
                                        )
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
