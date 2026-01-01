<script setup lang="ts">
import { ref, onMounted, computed, nextTick } from "vue";
import { usePage, useForm, router, Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AdminItemAction from "@/Components/AdminItemAction.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import DefaultTable from "@/Components/DefaultTable.vue";
import { useScreenSize } from "@/plugins/screen-size";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import OrderCard from "./OrderCard.vue";
import StatusChip from "@/Components/StatusChip.vue";
import CustomPageProps from "@/types/model/CustomPageProps";
import SearchInput from "@/Components/SearchInput.vue";
import { scrollToTop } from "@/plugins/helpers";
import { useDialogStore } from "@/stores/dialog-store";
import cookieManager from "@/plugins/cookie-manager";
import axios from "axios";

const screenSize = useScreenSize();

const props = defineProps({
    invoices: {
        type: Object as () => PaginationModel<InvoiceEntity>,
        required: true,
    },
    categories: {
        type: Array as () => CategoryEntity[],
        required: true,
    },
});

const page = usePage<CustomPageProps>();

const invoices = ref<PaginationModel<InvoiceEntity>>({
    ...props.invoices,
    data: props.invoices.data.map((invoice) => ({
        ...invoice,
        showDeleteModal: false,
    })),
});

const closeDeleteOrderDialog = (invoice, result) => {
    if (invoice) {
        invoice.showDeleteModal = false;
        if (result) {
            openSuccessDialog("Data deleted successfully");
        }
    }
};

const deleteOrder = (invoice) => {
    if (invoice) {
        const form = useForm({});
        form.delete(`/admin/order/${invoice.id}`, {
            onError: (errors) => {
                openErrorDialog(errors.error);
            },
            onSuccess: () => {
                closeDeleteOrderDialog(invoice, true);
                getOrders();
            },
        });
    }
};

const showSuccessDialog = ref(false);
const successMessage = ref("Data deleted successfully");

const openSuccessDialog = (message) => {
    successMessage.value = message;
    showSuccessDialog.value = true;
};

const showErrorDialog = ref(false);
const errorMessage = ref("");

const openErrorDialog = (message) => {
    errorMessage.value = message;
    showErrorDialog.value = true;
};

// Filters
const categories = (page.props.categories || []) as CategoryEntity[];
const categorySearch = ref("");

const filteredCategories = computed(() => {
    return categories.filter((category) =>
        category.title
            .toLowerCase()
            .includes(categorySearch.value?.toLowerCase() || "")
    );
});

const filters = ref({
    page: null,
    search: null,
    category_id: null,
    category: null,
});

const getQueryParams = () => {
    const urlParams = new URLSearchParams(window.location.search);
    filters.value.page = parseInt(urlParams.get("page")) || 1;
    filters.value.search = urlParams.get("search") || null;
    filters.value.category_id = parseInt(urlParams.get("category_id")) || null;
    filters.value.category =
        props.categories.find(
            (category) => category.id === filters.value.category_id
        ) || null;
};
getQueryParams();

const queryParams = computed(() => {
    return {
        page: filters.value.page || undefined,
        search: filters.value.search || undefined,
        category_id: filters.value.category_id || undefined,
    };
});

function getOrders() {
    router.get("/admin/order", queryParams.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            invoices.value = {
                ...page.props.invoices,
                data: page.props.invoices.data.map((invoice) => ({
                    ...invoice,
                    showDeleteModal: false,
                })),
            };
            scrollToTop({ id: "main-area" });
            setSearchFocus();
        },
    });
}

function setSearchFocus() {
    nextTick(() => {
        const input = document.getElementById(
            "search-order"
        ) as HTMLInputElement;
        input?.focus({ preventScroll: true });
    });
}

async function exportTransactionItemsExcel() {
    try {
        const token = `Bearer ${cookieManager.getItem("access_token")}`;
        await axios
            .get("/admin/transaction-item-export", {
                headers: {
                    Authorization: token,
                },
                responseType: "blob",
            })
            .then((response) => {
                useDialogStore().openSuccessDialog(
                    "Transaction item data successfully exported."
                );

                // Create a link to download the file
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute("download", "transaction_items.xlsx");
                document.body.appendChild(link);
                link.click();
                link.remove();
            });
    } catch (error) {
        openErrorDialog("Failed to export data. Please try again.");
    }
}

onMounted(() => {
    if (page.props.flash.success) {
        openSuccessDialog(page.props.flash.success);
    }
    setSearchFocus();
});
</script>

<template>
    <AdminLayout title="Orders" :showTitle="true">
        <div class="p-4">
            <div class="flex items-center justify-between gap-4">
                <PrimaryButton
                    type="button"
                    class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                    @click="$inertia.visit('/admin/order/create')"
                >
                    Add
                </PrimaryButton>

                <div class="flex items-center gap-2">
                    <DropdownSearchInput
                        id="category_id"
                        :modelValue="
                            filters.category_id
                                ? {
                                      label: filters.category?.title,
                                      value: filters.category_id,
                                  }
                                : null
                        "
                        :options="
                            filteredCategories.map((category) => ({
                                label: category.title,
                                value: category.id,
                            }))
                        "
                        placeholder="Select Category"
                        class="max-w-48"
                        @update:modelValue="
                            (option) => {
                                filters.category_id = option?.value;
                                filters.category = option
                                    ? filteredCategories.find(
                                          (category) =>
                                              category.id === option.value
                                      )
                                    : null;
                                filters.page = 1;
                                getOrders();
                            }
                        "
                        @search="categorySearch = $event"
                        @clear="
                            filters.category_id = null;
                            filters.category = null;
                            categorySearch = '';
                            filters.page = 1;
                            getOrders();
                        "
                    />
                    <SearchInput
                        id="search-order"
                        v-model="filters.search"
                        placeholder="Search orders..."
                        class="max-w-48"
                        @search="
                            filters.page = 1;
                            getOrders();
                        "
                    />

                    <PrimaryButton @click="exportTransactionItemsExcel()">
                        Export
                        <template #prefix>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="16"
                                height="16"
                                viewBox="0 0 16 16"
                                fill="currentColor"
                                class="text-white size-5"
                            >
                                <path
                                    d="M13.75 7.0001V13.0001C13.75 13.3316 13.6183 13.6496 13.3839 13.884C13.1495 14.1184 12.8315 14.2501 12.5 14.2501H3.5C3.16848 14.2501 2.85054 14.1184 2.61612 13.884C2.3817 13.6496 2.25 13.3316 2.25 13.0001V7.0001C2.25 6.66858 2.3817 6.35064 2.61612 6.11622C2.85054 5.8818 3.16848 5.7501 3.5 5.7501H4.75C4.94891 5.7501 5.13968 5.82912 5.28033 5.96977C5.42098 6.11043 5.5 6.30119 5.5 6.5001C5.5 6.69902 5.42098 6.88978 5.28033 7.03043C5.13968 7.17109 4.94891 7.2501 4.75 7.2501H3.75V12.7501H12.25V7.2501H11.25C11.0511 7.2501 10.8603 7.17109 10.7197 7.03043C10.579 6.88978 10.5 6.69902 10.5 6.5001C10.5 6.30119 10.579 6.11043 10.7197 5.96977C10.8603 5.82912 11.0511 5.7501 11.25 5.7501H12.5C12.8315 5.7501 13.1495 5.8818 13.3839 6.11622C13.6183 6.35064 13.75 6.66858 13.75 7.0001ZM6.03062 4.53073L7.25 3.3126V8.5001C7.25 8.69902 7.32902 8.88978 7.46967 9.03043C7.61032 9.17108 7.80109 9.2501 8 9.2501C8.19891 9.2501 8.38968 9.17108 8.53033 9.03043C8.67098 8.88978 8.75 8.69902 8.75 8.5001V3.3126L9.96937 4.5326C10.0391 4.60237 10.122 4.65771 10.2131 4.69546C10.3043 4.73322 10.402 4.75265 10.5006 4.75265C10.5993 4.75265 10.697 4.73322 10.7881 4.69546C10.8793 4.65771 10.9621 4.60237 11.0319 4.5326C11.1016 4.46284 11.157 4.38002 11.1947 4.28886C11.2325 4.19771 11.2519 4.10002 11.2519 4.00135C11.2519 3.90269 11.2325 3.80499 11.1947 3.71384C11.157 3.62269 11.1016 3.53987 11.0319 3.4701L8.53187 0.970103C8.4622 0.900183 8.3794 0.844705 8.28824 0.806851C8.19707 0.768997 8.09934 0.749512 8.00062 0.749512C7.90191 0.749512 7.80418 0.768997 7.71301 0.806851C7.62185 0.844705 7.53905 0.900183 7.46938 0.970103L4.96938 3.4701C4.89961 3.53987 4.84427 3.62269 4.80651 3.71384C4.76876 3.80499 4.74932 3.90269 4.74932 4.00135C4.74932 4.20061 4.82848 4.39171 4.96938 4.5326C5.11027 4.6735 5.30137 4.75265 5.50063 4.75265C5.69988 4.75265 5.89098 4.6735 6.03188 4.5326L6.03062 4.53073Z"
                                    fill="currentColor"
                                />
                            </svg>
                        </template>
                    </PrimaryButton>
                </div>
            </div>

            <!-- Table -->
            <DefaultTable
                v-if="screenSize.is('xl')"
                :isEmpty="invoices.data.length === 0"
                class="mt-6"
            >
                <template #thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Date</th>
                        <th>Code</th>
                        <th>Customer</th>
                        <th>Item</th>
                        <th>Payment</th>
                        <th>Shipping</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="w-24">Action</th>
                    </tr>
                </template>
                <template #tbody>
                    <tr
                        v-for="(invoice, index) in invoices.data"
                        :key="invoice.id"
                    >
                        <td>
                            {{
                                index +
                                1 +
                                (props.invoices.current_page - 1) *
                                    props.invoices.per_page
                            }}
                        </td>
                        <td>
                            {{ $formatDate(invoice.created_at) }}
                        </td>
                        <td>
                            <Link
                                :href="`/admin/order/${invoice.id}`"
                                class="hover:underline"
                            >
                                {{ invoice.code }}
                            </Link>
                        </td>
                        <td class="whitespace-normal!">
                            {{ invoice.transaction.user.name }}
                        </td>
                        <td>
                            {{ invoice.transaction.items.length }}
                        </td>
                        <td>
                            {{ invoice.transaction.payment_method.name }}
                        </td>
                        <td>
                            {{ invoice.transaction.shipping_method.name }}
                        </td>
                        <td>
                            {{ $formatCurrency(invoice.amount) }}
                        </td>
                        <td>
                            <StatusChip
                                :status="invoice.status"
                                :label="invoice.status.toLocaleUpperCase()"
                                class="w-fit"
                            />
                        </td>
                        <td>
                            <AdminItemAction
                                @edit="
                                    $inertia.visit(`/admin/order/${invoice.id}`)
                                "
                            />
                        </td>
                    </tr>
                </template>
            </DefaultTable>

            <!-- Mobile View -->
            <div v-if="!screenSize.is('xl')" class="flex flex-col gap-3 mt-4">
                <template v-if="invoices.data.length > 0">
                    <div
                        v-for="(invoice, index) in invoices.data"
                        :key="invoice.id"
                    >
                        <OrderCard
                            :invoice="invoice"
                            @edit="$inertia.visit(`/admin/order/${invoice.id}`)"
                        />
                    </div>
                </template>
                <div v-else class="flex items-center justify-center py-10">
                    <p class="text-sm text-center text-gray-500">
                        No data found.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="invoices.total > 0" class="flex flex-col gap-2 mt-4">
                <p class="text-xs text-gray-500 sm:text-sm">
                    Showing {{ invoices.from }} - {{ invoices.to }} of
                    {{ invoices.total }} items
                </p>
                <DefaultPagination
                    :isApi="true"
                    :links="invoices.links"
                    @change="
                        (page) => {
                            filters.page = page;
                            getOrders();
                        }
                    "
                />
            </div>

            <SuccessDialog
                :show="showSuccessDialog"
                :title="successMessage"
                @close="showSuccessDialog = false"
            />

            <ErrorDialog
                :show="showErrorDialog"
                @close="showErrorDialog = false"
            >
                <template #content>
                    <div>
                        <div
                            class="mb-1 text-lg font-medium text-center text-gray-900"
                        >
                            An Error Occurred
                        </div>
                        <p class="text-center text-gray-700">
                            {{ errorMessage }}
                        </p>
                    </div>
                </template>
            </ErrorDialog>
        </div>
    </AdminLayout>
</template>
