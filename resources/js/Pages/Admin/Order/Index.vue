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

onMounted(() => {
    if (page.props.flash.success) {
        openSuccessDialog(page.props.flash.success);
    }
    setSearchFocus();
});
</script>

<template>
    <AdminLayout title="Orders" :showTitle="true">
        <DefaultCard :isMain="true">
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
        </DefaultCard>
    </AdminLayout>
</template>
