<script setup lang="ts">
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import { getImageUrl } from "@/plugins/helpers";
import DiscountTag from "@/Components/DiscountTag.vue";

const props = defineProps({
    item: {
        type: Object as () => TransactionItemEntity,
        required: true,
    },
    showDivider: {
        type: Boolean,
        default: true,
    },
});

function formatPrice(price = 0) {
    return price.toLocaleString("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    });
}
</script>

<template>
    <div
        class="flex items-start w-full py-4 border-b gap-x-4 border-gray-200"
        :class="{
            'border-none': !props.showDivider,
        }"
    >
        <div class="flex items-center justify-center shrink-0 gap-4">
            <!-- Image -->
            <img
                v-if="props.item.image"
                :src="getImageUrl(props.item.image)"
                alt="Product Image"
                class="object-cover size-20 sm:size-[120px] rounded-lg outline outline-gray-300"
            />
            <div
                v-else
                class="flex items-center justify-center size-20 sm:size-[120px] bg-gray-100 rounded-lg aspect-square"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    class="size-6 fill-gray-400"
                >
                    <path
                        d="M5 21C4.45 21 3.97933 20.8043 3.588 20.413C3.19667 20.0217 3.00067 19.5507 3 19V5C3 4.45 3.196 3.97933 3.588 3.588C3.98 3.19667 4.45067 3.00067 5 3H19C19.55 3 20.021 3.196 20.413 3.588C20.805 3.98 21.0007 4.45067 21 5V19C21 19.55 20.8043 20.021 20.413 20.413C20.0217 20.805 19.5507 21.0007 19 21H5ZM6 17H18L14.25 12L11.25 16L9 13L6 17Z"
                    />
                </svg>
            </div>
        </div>

        <div class="flex flex-col w-full gap-2">
            <!-- Detail -->
            <Link :href="`/book/${props.item.book.slug}`">
                <h3
                    class="w-full text-sm font-medium text-gray-800 sm:text-base hover:text-primary-dark"
                >
                    {{ props.item.book.title }}
                </h3>
            </Link>

            <div class="flex items-center gap-x-2">
                <DiscountTag
                    v-if="props.item.book.discount > 0"
                    discount-type="percentage"
                    :discount="props.item.book.discount"
                    class="text-xs! px-1! py-0.5!"
                />
                <p class="text-sm text-gray-800">
                    {{ formatPrice(props.item.book.final_price) }}
                </p>
                <p
                    v-if="props.item.book.discount > 0"
                    class="text-xs text-gray-500 line-through"
                >
                    {{ formatPrice(props.item.book.slice_price) }}
                </p>
            </div>

            <!-- Quantity and Total Price -->
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-gray-800 text-start">
                    x {{ props.item.quantity }} pcs
                </p>
                <p class="text-sm font-semibold text-gray-800 text-end">
                    {{ $formatCurrency(props.item.subtotal) }}
                </p>
            </div>
        </div>
    </div>
</template>
