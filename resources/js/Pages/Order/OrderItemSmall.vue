<script setup lang="ts">
import DiscountTag from "@/Components/DiscountTag.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Rating from "@/Components/Rating.vue";
import RatingSmall from "@/Components/RatingSmall.vue";
import { getImageUrl } from "@/plugins/helpers";
import { ref } from "vue";
import BookReviewForm from "./BookReviewForm.vue";
import DialogModal from "@/Components/DialogModal.vue";

const props = defineProps({
    item: {
        type: Object as () => TransactionItemEntity,
        required: true,
    },
    showDivider: {
        type: Boolean,
        default: false,
    },
});

function formatPrice(price = 0) {
    return price.toLocaleString("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    });
}

const showBookReviewForm = ref(false);

function refresh() {
    window.location.reload();
}
</script>

<template>
    <div
        class="flex flex-col items-start justify-center w-full gap-4 py-2.5 border-b border-gray-200"
        :class="{
            'border-none': !props.showDivider,
        }"
    >
        <div class="flex items-start w-full gap-x-4">
            <div
                class="flex items-center justify-center shrink-0 gap-4 max-sm:mt-1"
            >
                <!-- Image -->
                <img
                    v-if="props.item.image"
                    :src="getImageUrl(props.item.image)"
                    alt="Product"
                    class="object-cover w-[60px] sm:w-20 aspect-3/4 rounded-lg outline outline-gray-300"
                />
                <div
                    v-else
                    class="flex items-center justify-center w-[60px] sm:w-20 bg-gray-100 rounded-lg aspect-3/4"
                >
                    <div
                        class="flex flex-col items-center justify-center gap-1"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="lucide lucide-book-open w-6 h-6 text-muted-foreground/50"
                        >
                            <path d="M12 7v14"></path>
                            <path
                                d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"
                            ></path>
                        </svg>
                        <span
                            class="text-xs text-center text-muted-foreground hidden sm:block"
                            >No Cover</span
                        >
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-start w-full gap-1">
                <!-- Detail -->
                <p class="text-sm font-medium text-gray-800 sm:text-base">
                    {{ props.item.book.title }}
                </p>

                <div class="flex items-center gap-x-2">
                    <DiscountTag
                        v-if="props.item.book.discount > 0"
                        discount-type="percentage"
                        :discount="props.item.book.discount"
                        class="text-xs!"
                    />
                    <p class="text-xs text-gray-800 sm:text-sm">
                        {{ formatPrice(props.item.book.final_price) }}
                    </p>
                    <p
                        v-if="props.item.book.discount > 0"
                        class="text-xs text-gray-500 line-through"
                    >
                        {{ formatPrice(props.item.book.slice_price) }}
                    </p>
                </div>

                <div class="flex items-center justify-between w-full gap-x-4">
                    <p class="text-sm font-semibold text-gray-800 text-end">
                        x {{ props.item.quantity }} pcs
                    </p>
                    <p class="text-sm font-semibold text-gray-800 text-end">
                        {{ $formatCurrency(props.item.subtotal) }}
                    </p>
                </div>

                <template v-if="props.item.fullfillment_status === 'completed'">
                    <!-- Review -->
                    <div
                        v-if="props.item.rating || props.item.review"
                        class="mt-2 p-2.5 border border-gray-200 rounded-lg bg-gray-50 w-full flex flex-col gap-2"
                    >
                        <div class="flex gap-2 items-center justify-between">
                            <RatingSmall
                                :rating="props.item.rating"
                                :showRatingCount="false"
                            />
                            <div class="text-xs text-gray-500">
                                {{ $formatDate(props.item.reviewed_at) }}
                            </div>
                        </div>

                        <p
                            v-if="props.item.review"
                            class="text-xs text-gray-700 whitespace-pre-line line-clamp-3"
                        >
                            {{ props.item.review }}
                        </p>
                        <div
                            v-if="
                                props.item.attachments &&
                                props.item.attachments.length > 0
                            "
                            class="flex items-center gap-2"
                        >
                            <template
                                v-for="(attachment, index) in props.item
                                    .attachments"
                                :key="index"
                            >
                                <img
                                    :src="getImageUrl(attachment.file_path)"
                                    alt="Review Attachment"
                                    class="object-cover size-16 rounded-lg outline outline-gray-300"
                                />
                            </template>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div
                        v-else-if="
                            $page.props.auth.user.id == props.item.user_id
                        "
                        class="flex items-center gap-2 mt-2"
                    >
                        <PrimaryButton
                            class="text-sm px-3 py-1.5"
                            @click.stop="showBookReviewForm = true"
                        >
                            Write a Review
                        </PrimaryButton>
                    </div>

                    <div
                        v-else
                        class="mt-2 p-2.5 border border-gray-200 rounded-lg bg-gray-50 w-full flex flex-col gap-2"
                    >
                        <p class="text-xs text-gray-500 italic">
                            No review for this item yet.
                        </p>
                    </div>
                </template>
            </div>
        </div>

        <DialogModal
            :show="showBookReviewForm"
            maxWidth="sm"
            @close="showBookReviewForm = false"
            @click.stop
        >
            <template #content>
                <BookReviewForm
                    :transactionItem="props.item"
                    @close="showBookReviewForm = false"
                    @submitted="
                        showBookReviewForm = false;
                        refresh();
                    "
                />
            </template>
        </DialogModal>
    </div>
</template>
