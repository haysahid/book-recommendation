<script setup lang="ts">
import { ref, computed } from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ChangeTransactionStatusDialog from "./ChangeTransactionStatusDialog.vue";
import axios from "axios";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import InvoiceDetail from "./InvoiceDetail.vue";
import OrderContentRow from "@/Components/OrderContentRow.vue";
import StatusChip from "@/Components/StatusChip.vue";
import cookieManager from "@/plugins/cookie-manager";

const props = defineProps({
    invoice: {
        type: Object as () => InvoiceEntity,
        required: true,
    },
    items: {
        type: Array as () => TransactionItemEntity[],
        required: true,
    },
    payments: {
        type: Array as () => PaymentEntity[],
        required: true,
    },
});

const showChangeStatusDialog = ref(false);

function changeStatus(newStatus: string) {
    const token = `Bearer ${cookieManager.getItem("access_token")}`;

    axios
        .put(
            "/api/admin/change-order-status",
            {
                invoice_id: props.invoice.id,
                status: newStatus,
            },
            {
                headers: {
                    Authorization: token,
                },
            }
        )
        .then(() => {
            window.location.reload();
        })
        .catch((error) => {
            console.error("Error changing transaction status:", error);
        });
}

const showPaymentActions = computed(() => {
    return true;

    return (
        props.invoice.transaction.status === "pending" &&
        props.invoice.transaction.payment_method.slug === "transfer"
    );
});

const payment = computed(() => {
    return props.payments.length > 0 ? props.payments[0] : null;
});

window.onpopstate = function () {
    location.reload();
};
</script>

<template>
    <AdminLayout
        title="Order Details"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Orders', url: '/admin/order', active: false },
            { text: props.invoice.code, active: true },
        ]"
    >
        <DefaultCard :isMain="true">
            <InvoiceDetail
                :invoice="props.invoice"
                :items="props.items"
                class="px-0! pt-0! md:px-0!"
                :showTracking="props.invoice.status !== 'cancelled'"
                :isShowingFromMyStore="true"
            >
                <template #additionalInfo>
                    <!-- Payment -->
                    <template v-if="showPaymentActions">
                        <div class="my-2 border-b border-gray-300"></div>
                        <OrderContentRow
                            label="Payment Status"
                            :value="payment?.status"
                        >
                            <template #value>
                                <StatusChip
                                    :status="payment.status"
                                    :label="payment.status?.toUpperCase()"
                                />
                            </template>
                        </OrderContentRow>
                        <OrderContentRow
                            v-if="payment?.midtrans_response"
                            label="Payment Type"
                            :value="
                                payment?.midtrans_response?.payment_type
                                    ?.split('_')
                                    .map(
                                        (word) =>
                                            word.charAt(0).toUpperCase() +
                                            word.slice(1)
                                    )
                                    .join(' ')
                            "
                        />
                        <OrderContentRow
                            v-if="payment?.midtrans_response?.va_numbers"
                            label="Payment Destination"
                            :value="
                                payment?.midtrans_response?.va_numbers[0]?.bank?.toUpperCase()
                            "
                        />
                        <OrderContentRow
                            v-if="payment?.midtrans_response"
                            label="Payment Expiry"
                            :value="payment?.midtrans_response?.expiry_time"
                        />
                    </template>

                    <!-- Shipping Address -->
                    <template
                        v-if="
                            props.invoice.transaction.shipping_method.slug ===
                            'courier'
                        "
                    >
                        <div class="my-2 border-b border-gray-300"></div>
                        <OrderContentRow
                            label="Province"
                            :value="props.invoice.transaction.province_name"
                        />
                        <OrderContentRow
                            label="City"
                            :value="props.invoice.transaction.city_name"
                        />
                        <OrderContentRow
                            label="District"
                            :value="props.invoice.transaction.district_name"
                        />
                        <OrderContentRow
                            label="Subdistrict"
                            :value="props.invoice.transaction.subdistrict_name"
                        />
                        <OrderContentRow
                            label="Zip Code"
                            :value="props.invoice.transaction.zip_code"
                        />
                        <OrderContentRow
                            label="Address"
                            :value="props.invoice.transaction.address"
                        />
                    </template>
                </template>

                <template #actions v-if="props.invoice.status !== 'cancelled'">
                    <PrimaryButton
                        @click="showChangeStatusDialog = true"
                        class="w-full py-3"
                    >
                        Change Status
                    </PrimaryButton>
                </template>
            </InvoiceDetail>
        </DefaultCard>

        <ChangeTransactionStatusDialog
            :show="showChangeStatusDialog"
            :currentStatus="props.invoice.status"
            :options="[
                {
                    value: 'pending',
                    label: 'PENDING',
                    disabled: false,
                },
                {
                    value: 'paid',
                    label: 'PAID',
                    disabled: false,
                },
                {
                    value: 'processing',
                    label: 'PROCESSING',
                    disabled: false,
                },
                {
                    value: 'completed',
                    label: 'COMPLETED',
                    disabled: false,
                },
                {
                    value: 'cancelled',
                    label: 'CANCELLED',
                    disabled: false,
                },
            ]"
            @change="
                showChangeStatusDialog = false;
                if ($event != props.invoice.status) {
                    changeStatus($event);
                }
            "
            @close="showChangeStatusDialog = false"
        />
    </AdminLayout>
</template>
