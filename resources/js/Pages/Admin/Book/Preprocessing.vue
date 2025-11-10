<script setup lang="ts">
import DefaultCard from "@/Components/DefaultCard.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DefaultTable from "@/Components/DefaultTable.vue";
import axios from "axios";
import { onMounted, ref } from "vue";
import cookieManager from "@/plugins/cookie-manager";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import useBookService from "@/services/book-service";

const books = ref<PaginationModel<BookEntity>>(null);
const selectedBookIds = ref<number[]>([]);

const filters = ref({
    page: 1,
    search: null,
    limit: 10,
});

function getBooks() {
    axios
        .get("/api/admin/book", {
            params: {
                page: filters.value.page,
                search: filters.value.search,
                limit: filters.value.limit,
                order_by: "created_at",
                order_direction: "desc",
            },
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${cookieManager.getItem(
                    "access_token"
                )}`,
            },
        })
        .then((response) => {
            books.value = response.data.result;
        })
        .catch((error) => {
            console.error("Error fetching books:", error);
        });
}

const bookService = useBookService();

onMounted(() => {
    getBooks();
});
</script>

<template>
    <AdminLayout title="Pre-processing" :showTitle="true">
        <div class="p-4">
            <h1 class="text-2xl font-bold mb-4">Pre-processing Data</h1>
            <p class="text-base mb-6 text-gray-600">
                Select books or leave unselected to process all books.
            </p>

            <DefaultCard class="mb-6">
                <div
                    class="flex flex-col sm:flex-row gap-2.5 sm:items-start sm:justify-between mb-4"
                >
                    <div>
                        <h3 class="text-lg font-semibold">Cleaning Titles</h3>
                        <p
                            v-if="selectedBookIds.length > 0"
                            class="text-sm text-gray-500"
                        >
                            {{ selectedBookIds.length }} selected
                        </p>
                    </div>
                    <div class="flex gap-2 items-center">
                        <PrimaryButton
                            @click="
                                bookService.cleanBookTitles(selectedBookIds, {
                                    onSuccess: () => {
                                        getBooks();
                                    },
                                })
                            "
                        >
                            Clean Titles
                        </PrimaryButton>
                    </div>
                </div>
                <DefaultTable>
                    <template #thead>
                        <tr>
                            <th class="text-left w-12">
                                <input
                                    type="checkbox"
                                    :modelValue="
                                        selectedBookIds.length ===
                                        books?.data.length
                                    "
                                    @change="
                                        (e: Event) => {
                                            const target = e.target as HTMLInputElement;
                                            if (target.checked) {
                                                selectedBookIds = [
                                                    ...books?.data.map((book) => book.id),
                                                ];
                                            } else {
                                                selectedBookIds = [];
                                            }
                                        }
                                    "
                                />
                            </th>
                            <th class="text-left w-12">No</th>
                            <th class="text-left">Title</th>
                            <th class="text-left">Cleaned Title</th>
                        </tr>
                    </template>
                    <template #tbody>
                        <tr v-for="(book, index) in books?.data" :key="book.id">
                            <td>
                                <input
                                    type="checkbox"
                                    :value="book.id"
                                    v-model="selectedBookIds"
                                />
                            </td>
                            <td>
                                {{
                                    index +
                                    1 +
                                    (books.current_page - 1) * books.per_page
                                }}
                            </td>
                            <td>{{ book.title }}</td>
                            <td>{{ book.cleaned_title || "-" }}</td>
                        </tr>
                    </template>
                </DefaultTable>
                <div v-if="books?.links" class="mt-4">
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
            </DefaultCard>

            <DefaultCard>
                <div
                    class="flex flex-col sm:flex-row gap-2.5 sm:items-start sm:justify-between mb-4"
                >
                    <div>
                        <h3 class="text-lg font-semibold">Stemming Titles</h3>
                        <p
                            v-if="selectedBookIds.length > 0"
                            class="text-sm text-gray-500"
                        >
                            {{ selectedBookIds.length }} selected
                        </p>
                    </div>
                    <div class="flex gap-2 items-center">
                        <PrimaryButton
                            @click="
                                bookService.stemBookTitles(selectedBookIds, {
                                    onSuccess: () => {
                                        getBooks();
                                    },
                                })
                            "
                        >
                            Stem Titles
                        </PrimaryButton>
                    </div>
                </div>
                <DefaultTable>
                    <template #thead>
                        <tr>
                            <th class="text-left w-12">
                                <input
                                    type="checkbox"
                                    :modelValue="
                                        selectedBookIds.length ===
                                        books?.data.length
                                    "
                                    @change="
                                        (e: Event) => {
                                            const target = e.target as HTMLInputElement;
                                            if (target.checked) {
                                                selectedBookIds = [
                                                    ...books?.data.map((book) => book.id),
                                                ];
                                            } else {
                                                selectedBookIds = [];
                                            }
                                        }
                                    "
                                />
                            </th>
                            <th class="text-left w-12">No</th>
                            <th class="text-left">Cleaned Title</th>
                            <th class="text-left">Stemmed Title</th>
                        </tr>
                    </template>
                    <template #tbody>
                        <tr v-for="(book, index) in books?.data" :key="book.id">
                            <td>
                                <input
                                    type="checkbox"
                                    :value="book.id"
                                    v-model="selectedBookIds"
                                />
                            </td>
                            <td>
                                {{
                                    index +
                                    1 +
                                    (books.current_page - 1) * books.per_page
                                }}
                            </td>
                            <td>{{ book.cleaned_title || "-" }}</td>
                            <td>{{ book.stemmed_title || "-" }}</td>
                        </tr>
                    </template>
                </DefaultTable>
                <div v-if="books?.links" class="mt-4">
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
            </DefaultCard>
        </div>
    </AdminLayout>
</template>
