<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { computed, onMounted, ref } from "vue";
import { router } from "@inertiajs/vue3";
import CustomPageProps from "@/types/model/CustomPageProps";
import cookieManager from "@/plugins/cookie-manager";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import BookCard from "./BookCard.vue";
import SearchInput from "@/Components/SearchInput.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import { scrollToTop } from "@/plugins/helpers";

const props = defineProps({
    books: {
        type: Object as () => PaginationModel<BookEntity>,
        required: true,
    },
    categories: {
        type: Array as () => CategoryModel[],
        required: true,
    },
});

const categories = ref<CategoryModel[]>(props.categories);

const books = ref<PaginationModel<BookEntity>>(props.books);
const selectedBookIds = ref<number[]>([]);

const filters = ref({
    page: null,
    search: null,
    limit: null,
    category_slug: null,
});

const selectedCategory = computed(() => {
    return categories.value.find(
        (category) => category.slug === filters.value.category_slug
    );
});

const currentSearch = ref(null);

function getQueryParams() {
    const urlParams = new URLSearchParams(window.location.search);
    filters.value.page = parseInt(urlParams.get("page")) || 1;
    filters.value.search = urlParams.get("search") || null;
    filters.value.limit = urlParams.get("limit") || null;
    filters.value.category_slug = urlParams.get("category_slug") || null;

    currentSearch.value = filters.value.search;
}

const queryParams = computed(() => {
    return {
        page: filters.value.page || undefined,
        search: filters.value.search || undefined,
        limit: filters.value.limit || undefined,
        category_slug: filters.value.category_slug || undefined,
    };
});

function getBooks() {
    router.get("/admin/book", queryParams.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page: CustomPageProps) => {
            books.value = page.props.books;
            getQueryParams();
            scrollToTop({ id: "main-area" });
        },
        headers: {
            Accept: "application/json",
            Authorization: `Bearer ${cookieManager.getItem("access_token")}`,
        },
    });
}

onMounted(() => {
    getQueryParams();
});
</script>

<template>
    <AdminLayout title="Books" :showTitle="true">
        <div class="p-4">
            <div
                class="flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between mb-6"
            >
                <div>
                    <h1 class="text-2xl font-bold mb-1">Books</h1>
                    <p class="text-base text-gray-600">Source: Gramedia</p>
                </div>
                <div class="flex gap-3 items-center w-full sm:w-auto">
                    <DropdownSearchInput
                        id="category"
                        :useSearchDebounce="true"
                        :modelValue="
                            selectedCategory
                                ? {
                                      label: selectedCategory.title,
                                      value: selectedCategory.slug,
                                  }
                                : null
                        "
                        :options="
                            categories.map((category) => ({
                                label: category.title,
                                value: category.slug,
                            }))
                        "
                        :searchable="true"
                        placeholder="Category"
                        class="max-w-48"
                        @update:modelValue="
                            (option) => {
                                filters.category_slug = option.value;
                                getBooks();
                            }
                        "
                        @clear="
                            filters.category_slug = null;
                            getBooks();
                        "
                    />
                    <SearchInput
                        id="search-book"
                        v-model="filters.search"
                        placeholder="Search..."
                        class="w-full sm:max-w-48"
                        :autofocus="true"
                        @search="
                            filters.page = 1;
                            getBooks();
                        "
                    />
                    <!-- <PrimaryButton @click=""> Add Book </PrimaryButton> -->
                </div>
            </div>

            <!-- Current Search -->
            <div v-if="currentSearch" class="mb-5 flex flex-col gap-2">
                <p class="text-2xl text-gray-800">
                    Showing results for
                    <span class="font-medium">"{{ currentSearch }}"</span>
                </p>
                <!-- 1758 results found -->
                <p class="text-sm text-gray-500">
                    {{ books.total }} results found
                </p>
            </div>

            <div class="mb-6">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
                >
                    <a
                        v-for="(book, index) in books.data"
                        :key="book.id"
                        :href="`https://www.gramedia.com/products/${book.slug}`"
                        target="_blank"
                    >
                        <BookCard :index="index" :book="book" class="h-full" />
                    </a>
                </div>

                <div v-if="books?.links" class="mt-6">
                    <!-- Showing -->
                    <p class="text-sm text-gray-500 mb-2">
                        Showing
                        {{ books.from }}
                        to
                        {{ books.to }}
                        of {{ books.total }} entries
                    </p>
                    <DefaultPagination
                        :links="books?.links"
                        :isApi="true"
                        @change="
                        (newPage: number) => {
                            filters.page = newPage;
                            getBooks();
                        }"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
