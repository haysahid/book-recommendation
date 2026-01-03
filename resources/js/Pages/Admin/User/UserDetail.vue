<script setup lang="ts">
import DefaultCard from "@/Components/DefaultCard.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import SummaryCard from "@/Components/SummaryCard.vue";
import { ref } from "vue";
import ThreeDotsLoading from "@/Components/ThreeDotsLoading.vue";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import UserProfile from "./UserProfile.vue";
import axios from "axios";
import cookieManager from "@/plugins/cookie-manager";
import Order from "@/Pages/Order/Order.vue";
import BookCard from "../Book/BookCard.vue";
import StatusChip from "@/Components/StatusChip.vue";

const props = defineProps({
    user: Object as () => UserEntity,
    stats: Object as () => {
        total_orders: number;
        total_spent: number;
    },
});

const invoices = ref<PaginationModel<InvoiceEntity>>(null);
const getInvoicesStatus = ref(null);
const invoicesFilter = ref({
    page: 1,
    limit: 3,
});

function getInvoices() {
    getInvoicesStatus.value = "loading";
    axios
        .get(`/api/admin/invoice`, {
            params: {
                user_id: props.user.id,
                page: invoicesFilter.value.page,
                limit: invoicesFilter.value.limit,
            },
            headers: {
                Authorization: `Bearer ${cookieManager.getItem(
                    "access_token"
                )}`,
            },
        })
        .then((response) => {
            invoices.value = response.data.result;
            getInvoicesStatus.value = "success";
        })
        .catch((error) => {
            console.error("Error fetching invoices:", error);
            getInvoicesStatus.value = "error";
        });
}
getInvoices();

const userRecommendedResult = ref<UserRecommendBookModel>(null);
const getRecommendedBooksStatus = ref(null);
function getRecommendedBooks() {
    getRecommendedBooksStatus.value = "loading";
    axios
        .get(`/api/admin/recommended-books/${props.user.id}`, {
            params: {
                limit: 8,
            },
            headers: {
                Authorization: `Bearer ${cookieManager.getItem(
                    "access_token"
                )}`,
            },
        })
        .then((response) => {
            userRecommendedResult.value = response.data.result;
            getRecommendedBooksStatus.value = "success";
        })
        .catch((error) => {
            console.error("Error fetching vouchers:", error);
            getRecommendedBooksStatus.value = "error";
        });
}
getRecommendedBooks();
</script>

<template>
    <AdminLayout
        title="User Detail"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Users', url: '/admin/user', active: false },
            { text: props.user.name, active: true },
        ]"
    >
        <div class="flex flex-col w-full gap-2 sm:gap-4 p-1.5 sm:p-0">
            <!-- Profile -->
            <UserProfile :user="props.user" />

            <!-- Summary -->
            <div class="grid w-full grid-cols-2 gap-2 sm:gap-4">
                <SummaryCard
                    title="Total Orders"
                    :value="$formatNumber(props.stats.total_orders)"
                />
                <SummaryCard
                    title="Total Spent"
                    :value="$formatCurrency(props.stats.total_spent)"
                />
            </div>

            <div class="flex flex-col w-full gap-1 lg:flex-row sm:gap-2">
                <!-- Orders -->
                <DefaultCard class="w-full">
                    <h3 class="font-semibold text-gray-900">Order History</h3>
                    <div class="w-full mt-2.5">
                        <div
                            v-if="invoices?.data?.length"
                            class="flex flex-col w-full gap-2 sm:gap-3"
                        >
                            <Order
                                v-for="(invoice, index) in invoices.data"
                                :key="invoice.id"
                                :invoice="invoice"
                                :showDivider="false"
                                :showStoreInfo="false"
                                :limitItems="2"
                                :items="
                                    invoice.transaction.items.filter(
                                        (item) =>
                                            item.store_id === invoice.store_id
                                    )
                                "
                            />
                        </div>
                        <div
                            v-else
                            class="flex items-center justify-center h-10 mb-6"
                        >
                            <ThreeDotsLoading
                                v-if="getInvoicesStatus === 'loading'"
                            />
                            <p v-else class="text-sm text-center text-gray-500">
                                No data found.
                            </p>
                        </div>
                    </div>
                    <div
                        v-if="invoices?.total > 0"
                        class="flex flex-col gap-2 mt-4"
                    >
                        <p class="text-xs text-gray-500 sm:text-sm">
                            Showing {{ invoices.from }} - {{ invoices.to }} of
                            {{ invoices.total }} items
                        </p>
                        <DefaultPagination
                            :links="invoices.links"
                            :isApi="true"
                            @change="
                                (page) => {
                                    invoicesFilter.page = page;
                                    getInvoices();
                                }
                            "
                        />
                    </div>
                </DefaultCard>
            </div>

            <!-- Recommended Books -->
            <DefaultCard class="w-full bg-primary-light!">
                <div class="flex gap-3 mb-4 items-center">
                    <h3 class="font-semibold text-white">Recommended Books</h3>
                    <StatusChip
                        v-if="userRecommendedResult?.strategy"
                        :label="userRecommendedResult?.strategy"
                        :class="{
                            'text-blue-500! bg-blue-100!':
                                userRecommendedResult?.strategy ===
                                'Cold Start',
                            'text-purple-500! bg-purple-100!':
                                userRecommendedResult?.strategy ===
                                'SVD Matrix Factorization',
                        }"
                    />
                </div>
                <div class="w-full mt-2.5">
                    <div
                        v-if="userRecommendedResult?.results?.length"
                        class="grid gap-3 sm:gap-4 grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                    >
                        <a
                            v-for="(
                                book, index
                            ) in userRecommendedResult.results"
                            :key="book.id"
                            :href="`/book/${book.slug}`"
                        >
                            <BookCard
                                :index="index"
                                :book="book"
                                class="h-full"
                            />
                        </a>
                    </div>
                    <div
                        v-else
                        class="flex items-center justify-center h-10 mb-6"
                    >
                        <ThreeDotsLoading
                            v-if="getRecommendedBooksStatus === 'loading'"
                        />
                        <p v-else class="text-sm text-center text-gray-500">
                            No data found.
                        </p>
                    </div>
                </div>
            </DefaultCard>
        </div>
    </AdminLayout>
</template>
