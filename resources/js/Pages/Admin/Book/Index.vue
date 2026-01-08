<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { computed, onMounted, ref } from "vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import CustomPageProps from "@/types/model/CustomPageProps";
import cookieManager from "@/plugins/cookie-manager";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import BookCard from "./BookCard.vue";
import SearchInput from "@/Components/SearchInput.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import { scrollToTop } from "@/plugins/helpers";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import useBookService from "@/services/book-service";
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";
import { useDialogStore } from "@/stores/dialog-store";

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

const selectedBook = ref<BookEntity | null>(null);
const showDeleteBookDialog = ref(false);

const openDeleteBookDialog = (book: BookEntity) => {
    if (book) {
        selectedBook.value = book;
        showDeleteBookDialog.value = true;
    }
};

const closeDeleteBookDialog = (result = false) => {
    showDeleteBookDialog.value = false;
    if (result) {
        selectedBook.value = null;
        useDialogStore().openSuccessDialog("Data Berhasil Dihapus");
    }
};

const deleteBook = () => {
    if (selectedBook.value) {
        const form = useForm({});
        form.delete(`/admin/book/${selectedBook.value.id}`, {
            onError: (errors) => {
                useDialogStore().openErrorDialog(errors.error);
            },
            onSuccess: () => {
                closeDeleteBookDialog(true);
                getBooks();
            },
        });
    }
};

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
                    <p class="text-sm text-gray-600">Source: Gramedia</p>
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
                    <SecondaryButton
                        @click="useBookService().exportBooksExcel()"
                    >
                        Export
                        <template #prefix>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="16"
                                height="16"
                                viewBox="0 0 16 16"
                                fill="currentColor"
                                class="size-5"
                            >
                                <path
                                    d="M13.75 7.0001V13.0001C13.75 13.3316 13.6183 13.6496 13.3839 13.884C13.1495 14.1184 12.8315 14.2501 12.5 14.2501H3.5C3.16848 14.2501 2.85054 14.1184 2.61612 13.884C2.3817 13.6496 2.25 13.3316 2.25 13.0001V7.0001C2.25 6.66858 2.3817 6.35064 2.61612 6.11622C2.85054 5.8818 3.16848 5.7501 3.5 5.7501H4.75C4.94891 5.7501 5.13968 5.82912 5.28033 5.96977C5.42098 6.11043 5.5 6.30119 5.5 6.5001C5.5 6.69902 5.42098 6.88978 5.28033 7.03043C5.13968 7.17109 4.94891 7.2501 4.75 7.2501H3.75V12.7501H12.25V7.2501H11.25C11.0511 7.2501 10.8603 7.17109 10.7197 7.03043C10.579 6.88978 10.5 6.69902 10.5 6.5001C10.5 6.30119 10.579 6.11043 10.7197 5.96977C10.8603 5.82912 11.0511 5.7501 11.25 5.7501H12.5C12.8315 5.7501 13.1495 5.8818 13.3839 6.11622C13.6183 6.35064 13.75 6.66858 13.75 7.0001ZM6.03062 4.53073L7.25 3.3126V8.5001C7.25 8.69902 7.32902 8.88978 7.46967 9.03043C7.61032 9.17108 7.80109 9.2501 8 9.2501C8.19891 9.2501 8.38968 9.17108 8.53033 9.03043C8.67098 8.88978 8.75 8.69902 8.75 8.5001V3.3126L9.96937 4.5326C10.0391 4.60237 10.122 4.65771 10.2131 4.69546C10.3043 4.73322 10.402 4.75265 10.5006 4.75265C10.5993 4.75265 10.697 4.73322 10.7881 4.69546C10.8793 4.65771 10.9621 4.60237 11.0319 4.5326C11.1016 4.46284 11.157 4.38002 11.1947 4.28886C11.2325 4.19771 11.2519 4.10002 11.2519 4.00135C11.2519 3.90269 11.2325 3.80499 11.1947 3.71384C11.157 3.62269 11.1016 3.53987 11.0319 3.4701L8.53187 0.970103C8.4622 0.900183 8.3794 0.844705 8.28824 0.806851C8.19707 0.768997 8.09934 0.749512 8.00062 0.749512C7.90191 0.749512 7.80418 0.768997 7.71301 0.806851C7.62185 0.844705 7.53905 0.900183 7.46938 0.970103L4.96938 3.4701C4.89961 3.53987 4.84427 3.62269 4.80651 3.71384C4.76876 3.80499 4.74932 3.90269 4.74932 4.00135C4.74932 4.20061 4.82848 4.39171 4.96938 4.5326C5.11027 4.6735 5.30137 4.75265 5.50063 4.75265C5.69988 4.75265 5.89098 4.6735 6.03188 4.5326L6.03062 4.53073Z"
                                    fill="currentColor"
                                />
                            </svg>
                        </template>
                    </SecondaryButton>
                    <Link href="/admin/book/create" class="inline-block">
                        <PrimaryButton @click="$event.stopPropagation()">
                            Add
                        </PrimaryButton>
                    </Link>
                </div>
            </div>

            <!-- Current Search -->
            <div v-if="currentSearch" class="mb-5 flex flex-col gap-2">
                <p class="text-2xl text-gray-800">
                    Showing results for
                    <span class="font-medium">"{{ currentSearch }}"</span>
                </p>
                <p class="text-sm text-gray-500">
                    {{ $formatNumber(books.total) }} results found
                </p>
            </div>

            <div class="mb-6">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
                >
                    <a
                        v-for="(book, index) in books.data"
                        :key="book.id"
                        :href="`/book/${book.slug}`"
                    >
                        <Transition
                            name="fade"
                            mode="out-in"
                            appear
                            @before-enter="
                                    (el: HTMLElement) => {
                                        el.style.transitionDelay =
                                            index * 50 + 'ms';
                                    }
                                "
                            @after-enter="
                                    (el: HTMLElement) => {
                                        el.style.transitionDelay = '';
                                    }
                                "
                            @after-leave="
                                    (el: HTMLElement) => {
                                        el.style.transitionDelay = '';
                                    }
                                "
                        >
                            <BookCard
                                :index="index"
                                :book="book"
                                @edit="
                                    $inertia.get(`/admin/book/${book.id}/edit`)
                                "
                                @delete="openDeleteBookDialog(book)"
                                class="h-full"
                            />
                        </Transition>
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

        <DeleteConfirmationDialog
            :show="showDeleteBookDialog"
            :title="`Delete Book <b>${selectedBook?.title}</b>?`"
            :description="`This action cannot be undone.`"
            @close="closeDeleteBookDialog()"
            @delete="deleteBook()"
        />
    </AdminLayout>
</template>
