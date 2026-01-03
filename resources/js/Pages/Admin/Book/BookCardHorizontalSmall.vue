<script setup lang="ts">
import AdminItemAction from "@/Components/AdminItemAction.vue";
import RatingSmall from "@/Components/RatingSmall.vue";
import { computed, getCurrentInstance } from "vue";

const props = defineProps({
    book: {
        type: Object as () => BookEntity,
        required: true,
    },
    index: {
        type: Number,
        required: true,
    },
    hideEditButton: {
        type: Boolean,
        default: false,
    },
    hideDeleteButton: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["edit", "delete"]);

const hasEditCallback = computed(() => {
    return !!getCurrentInstance()?.vnode?.props?.["onEdit"];
});

const hasDeleteCallback = computed(() => {
    return !!getCurrentInstance()?.vnode?.props?.["onDelete"];
});
</script>

<template>
    <div
        class="flex flex-row items-start justify-between gap-4 p-4 transition-all duration-300 ease-in-out border border-gray-200 rounded-lg hover:border-primary-light hover:ring-1 hover:ring-primary-light relative group hover:shadow-lg cursor-pointer hover:-translate-y-1 bg-white"
    >
        <div class="flex flex-row items-center w-40 gap-4">
            <img
                v-if="props.book.image"
                :src="props.book.image"
                :alt="props.book.title"
                class="object-cover rounded w-full shrink-0 group-hover:scale-105 aspect-3/4 transition-all duration-300 ease-in-out"
            />
            <div
                v-else
                class="flex items-center justify-center w-full bg-gray-100 rounded aspect-3/4 shrink-0 group-hover:scale-105 transition-all duration-300 ease-in-out"
            >
                <div class="flex flex-col items-center justify-center gap-3">
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
                        class="lucide lucide-book-open w-12 h-12 text-muted-foreground/50"
                    >
                        <path d="M12 7v14"></path>
                        <path
                            d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"
                        ></path>
                    </svg>
                    <span class="text-sm text-muted-foreground">No Cover</span>
                </div>
            </div>
        </div>

        <div class="flex flex-col items-start justify-start w-full gap-1.5">
            <div class="flex gap-2 items-center">
                <!-- Ranking Badge -->
                <div
                    v-if="props.index < 3 && book.score"
                    class="inline-flex items-center gap-1 bg-primary text-white border-0 rounded-full shadow-none px-2 py-0.5 text-xs"
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
                        class="lucide lucide-star w-3 h-3 fill-current"
                    >
                        <path
                            d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"
                        ></path>
                    </svg>

                    #{{ props.index + 1 }}
                </div>

                <!-- Recommendation Score Badge -->
                <div
                    v-if="book.score"
                    class="inline-flex items-center gap-1 bg-white/90 text-foreground border border-border rounded-full shadow-none px-2 py-0.5 text-xs"
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
                        class="lucide lucide-thumbs-up w-3 h-3 text-primary"
                    >
                        <path
                            d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-7A2 2 0 0 0 19.66 7H14z"
                        ></path>
                        <path
                            d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"
                        ></path>
                    </svg>
                    {{ book.score.toFixed(2) }}
                </div>
            </div>

            <div class="flex items-center gap-2 pe-12">
                <p
                    class="text-sm font-bold text-gray-900 md:text-base group-hover:text-primary transition-colors duration-300 ease-in-out"
                >
                    {{ props.book.title }}
                </p>
            </div>
            <div
                class="flex flex-col text-sm text-gray-500 gap-x-6 gap-y-0.5 w-full"
            >
                <div
                    v-if="props.book.author"
                    class="flex items-center gap-0.5 text-xs"
                >
                    By {{ props.book.author }}
                </div>

                <!-- Price -->
                <div class="flex flex-col gap-2">
                    <div
                        class="flex flex-wrap items-center gap-2 justify-between w-full"
                    >
                        <p class="text-base font-semibold text-gray-900">
                            {{ $formatCurrency(props.book.final_price) }}
                        </p>
                        <RatingSmall
                            :rating="props.book.average_rating"
                            :ratingCount="props.book.rating_count"
                        />
                    </div>
                    <div
                        v-if="props.book.discount"
                        class="flex gap-2 items-center"
                    >
                        <div
                            class="text-xs font-medium text-red-600 bg-red-100 px-2 py-1 rounded-md"
                        >
                            {{ props.book.discount }}%
                        </div>
                        <span
                            class="text-sm font-semibold text-gray-400 line-through"
                        >
                            {{ $formatCurrency(props.book.slice_price) }}
                        </span>
                    </div>
                </div>

                <!-- Divider -->
                <div
                    v-if="props.book.store_name || props.book.isbn"
                    class="border-b border-gray-100 my-2"
                ></div>
                <div
                    v-if="props.book.store_name"
                    class="flex items-center gap-0.5 text-xs"
                >
                    {{ props.book.store_name }}
                </div>
                <div
                    v-if="props.book.isbn"
                    class="flex items-center gap-0.5 text-xs"
                >
                    ISBN: {{ props.book.isbn }}
                </div>
            </div>
        </div>

        <!-- Hover Glow Effect -->
        <div
            class="absolute inset-0 bg-linear-to-t from-primary/10 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"
        />

        <AdminItemAction
            v-if="hasEditCallback || hasDeleteCallback"
            :hideEditButton="props.hideEditButton"
            :hideDeleteButton="props.hideDeleteButton"
            class="absolute top-2.5 right-2.5 sm:top-4 sm:right-4"
            @edit="emit('edit')"
            @delete="emit('delete')"
        />
    </div>
</template>
