<script setup lang="ts">
import DefaultCard from "@/Components/DefaultCard.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import SummaryCard from "@/Components/SummaryCard.vue";
import { ref } from "vue";
import ThreeDotsLoading from "@/Components/ThreeDotsLoading.vue";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import UserProfile from "./UserProfile.vue";

const props = defineProps({
    user: Object as () => UserEntity,
});

const pointTransactions = ref<PaginationModel<PointTransactionEntity>>(null);
const getPointTransactionsStatus = ref(null);
const pointTransactionFilter = ref({
    page: 1,
    limit: 5,
});

// function getPointTransactions() {
//     getPointTransactionsStatus.value = "loading";
//     axios
//         .get(`/api/admin/user/${props.user?.id}/point-transaction`, {
//             params: {
//                 page: pointTransactionFilter.value.page,
//                 limit: pointTransactionFilter.value.limit,
//             },
//             headers: {
//                 Authorization: `Bearer ${cookieManager.getItem(
//                     "access_token"
//                 )}`,
//             },
//         })
//         .then((response) => {
//             pointTransactions.value = response.data.result;
//             getPointTransactionsStatus.value = "success";
//         })
//         .catch((error) => {
//             console.error("Error fetching point transactions:", error);
//             getPointTransactionsStatus.value = "error";
//         });
// }
// getPointTransactions();

const userVouchers = ref<PaginationModel<UserVoucherEntity>>(null);
const getVouchersStatus = ref(null);
// function getUserVouchers() {
//     getVouchersStatus.value = "loading";
//     axios
//         .get(`/api/admin/user/${props.user.id}/voucher`, {
//             headers: {
//                 Authorization: `Bearer ${cookieManager.getItem(
//                     "access_token"
//                 )}`,
//             },
//         })
//         .then((response) => {
//             userVouchers.value = response.data.result;
//             getVouchersStatus.value = "success";
//         })
//         .catch((error) => {
//             console.error("Error fetching vouchers:", error);
//             getVouchersStatus.value = "error";
//         });
// }
// getUserVouchers();
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
        <div class="flex flex-col w-full gap-1 sm:gap-2 p-1.5 sm:p-0">
            <!-- Profile -->
            <UserProfile :user="props.user" />

            <!-- Summary -->
            <div class="grid w-full grid-cols-2 gap-1 lg:grid-cols-4 sm:gap-2">
                <SummaryCard title="Poin Saat Ini" :value="$formatNumber(0)" />
                <SummaryCard
                    title="Total Poin Diperoleh"
                    :value="$formatNumber(0)"
                />
                <SummaryCard title="Total Pesanan" :value="$formatNumber(0)" />
                <SummaryCard
                    title="Total Pengeluaran"
                    :value="$formatCurrency(0)"
                />
            </div>

            <div class="flex flex-col w-full gap-1 lg:flex-row sm:gap-2">
                <!-- Point Transactions -->
                <DefaultCard class="w-full">
                    <h3 class="font-semibold text-gray-900">Riwayat Poin</h3>
                    <div class="w-full mt-2.5">
                        <div
                            v-if="pointTransactions?.data?.length"
                            class="flex flex-col w-full gap-2"
                        >
                            <div
                                v-for="pointTransaction in pointTransactions.data"
                                :key="pointTransaction.id"
                                class="flex items-center justify-between gap-2.5 p-2.5 sm:gap-3 sm:p-4 transition-all duration-300 ease-in-out border border-gray-200 rounded-lg hover:border-primary-light hover:ring-1 hover:ring-primary-light"
                            >
                                <div class="flex flex-col">
                                    <p class="font-medium text-gray-900">
                                        {{ pointTransaction.description }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{
                                            $formatDate(
                                                pointTransaction.created_at
                                            )
                                        }}
                                    </p>
                                </div>
                                <p
                                    class="text-lg font-bold text-center"
                                    :class="{
                                        'text-green-600':
                                            pointTransaction.points_amount > 0,
                                        'text-red-600':
                                            pointTransaction.points_amount < 0,
                                    }"
                                >
                                    {{
                                        pointTransaction.points_amount > 0
                                            ? "+" +
                                              $formatNumber(
                                                  pointTransaction.points_amount
                                              )
                                            : $formatNumber(
                                                  pointTransaction.points_amount
                                              )
                                    }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-else
                            class="flex items-center justify-center h-[10vh] mb-6"
                        >
                            <ThreeDotsLoading
                                v-if="getPointTransactionsStatus === 'loading'"
                            />
                            <p v-else class="text-sm text-center text-gray-500">
                                Data tidak ditemukan.
                            </p>
                        </div>
                    </div>
                    <div
                        v-if="pointTransactions?.total > 0"
                        class="flex flex-col gap-2 mt-4"
                    >
                        <p class="text-xs text-gray-500 sm:text-sm">
                            Menampilkan {{ pointTransactions.from }} -
                            {{ pointTransactions.to }} dari
                            {{ pointTransactions.total }} item
                        </p>
                        <DefaultPagination
                            :links="pointTransactions.links"
                            :isApi="true"
                            @change="
                                (page) => {
                                    pointTransactionFilter.page = page;
                                    // getPointTransactions();
                                }
                            "
                        />
                    </div>
                </DefaultCard>

                <!-- Voucher -->
                <DefaultCard class="w-full h-fit">
                    <h3 class="font-semibold text-gray-900">Voucher</h3>
                    <div class="w-full mt-2.5">
                        <div
                            v-if="userVouchers && userVouchers.data.length"
                            class="flex flex-col w-full gap-2"
                        >
                            <div
                                v-for="userVoucher in userVouchers.data"
                                :key="userVoucher.id"
                                class="flex items-center gap-2.5 p-3 border border-gray-200 rounded-lg"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="26"
                                    height="26"
                                    viewBox="0 0 26 26"
                                    class="fill-gray-500 size-8 shrink-0"
                                >
                                    <path
                                        d="M4.33317 4.33325C3.75853 4.33325 3.20743 4.56153 2.80111 4.96785C2.39478 5.37418 2.1665 5.92528 2.1665 6.49992V10.8333C2.74114 10.8333 3.29224 11.0615 3.69857 11.4679C4.1049 11.8742 4.33317 12.4253 4.33317 12.9999C4.33317 13.5746 4.1049 14.1257 3.69857 14.532C3.29224 14.9383 2.74114 15.1666 2.1665 15.1666V19.4999C2.1665 20.0746 2.39478 20.6257 2.80111 21.032C3.20743 21.4383 3.75853 21.6666 4.33317 21.6666H21.6665C22.2411 21.6666 22.7922 21.4383 23.1986 21.032C23.6049 20.6257 23.8332 20.0746 23.8332 19.4999V15.1666C23.2585 15.1666 22.7074 14.9383 22.3011 14.532C21.8948 14.1257 21.6665 13.5746 21.6665 12.9999C21.6665 12.4253 21.8948 11.8742 22.3011 11.4679C22.7074 11.0615 23.2585 10.8333 23.8332 10.8333V6.49992C23.8332 5.92528 23.6049 5.37418 23.1986 4.96785C22.7922 4.56153 22.2411 4.33325 21.6665 4.33325H4.33317ZM16.7915 7.58325L18.4165 9.20825L9.20817 18.4166L7.58317 16.7916L16.7915 7.58325ZM9.544 7.62659C10.6057 7.62659 11.4615 8.48242 11.4615 9.54409C11.4615 10.0526 11.2595 10.5404 10.8999 10.9C10.5403 11.2596 10.0526 11.4616 9.544 11.4616C8.48234 11.4616 7.6265 10.6058 7.6265 9.54409C7.6265 9.03553 7.82853 8.54781 8.18813 8.18821C8.54773 7.82861 9.03545 7.62659 9.544 7.62659ZM16.4557 14.5383C17.5173 14.5383 18.3732 15.3941 18.3732 16.4558C18.3732 16.9643 18.1711 17.452 17.8115 17.8116C17.4519 18.1712 16.9642 18.3733 16.4557 18.3733C15.394 18.3733 14.5382 17.5174 14.5382 16.4558C14.5382 15.9472 14.7402 15.4595 15.0998 15.0999C15.4594 14.7403 15.9471 14.5383 16.4557 14.5383Z"
                                    />
                                </svg>
                                <div class="flex flex-col w-full gap-1">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <p
                                            class="text-sm font-semibold text-gray-900"
                                        >
                                            {{ userVoucher.voucher.name }}
                                        </p>
                                        <div
                                            class="flex items-center justify-center"
                                        >
                                            <span
                                                v-if="
                                                    userVoucher.status ===
                                                    'active'
                                                "
                                                class="px-1.5 py-0.5 text-xs font-medium text-green-800 bg-green-100 rounded-md"
                                                >Aktif</span
                                            >
                                            <span
                                                v-else-if="
                                                    userVoucher.status ===
                                                    'used'
                                                "
                                                class="px-1.5 py-0.5 text-xs font-medium text-blue-800 bg-blue-100 rounded-md"
                                                >Terpakai</span
                                            >
                                            <span
                                                v-else-if="
                                                    userVoucher.status ===
                                                    'expired'
                                                "
                                                class="px-1.5 py-0.5 text-xs font-medium text-red-800 bg-red-100 rounded-md"
                                                >Kadaluarsa</span
                                            >
                                            <span
                                                v-else
                                                class="px-1.5 py-0.5 text-xs font-medium text-gray-800 bg-gray-100 rounded-md"
                                                >Tidak Aktif</span
                                            >
                                        </div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <p class="text-xs text-gray-600">
                                            {{ userVoucher.voucher.code }}
                                        </p>
                                        <p
                                            class="text-xs text-gray-600 text-nowrap"
                                        >
                                            {{ userVoucher.usage_count }}/{{
                                                userVoucher.voucher
                                                    .usage_limit ?? "∞"
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="flex items-center justify-center h-[10vh] mb-6"
                        >
                            <ThreeDotsLoading
                                v-if="getVouchersStatus === 'loading'"
                            />
                            <p v-else class="text-sm text-center text-gray-500">
                                Data tidak ditemukan.
                            </p>
                        </div>
                    </div>
                </DefaultCard>
            </div>
        </div>
    </AdminLayout>
</template>
