<script setup lang="ts">
import { computed, useSlots } from "vue";
import Checkbox from "@/Components/Checkbox.vue";
import { Link } from "@inertiajs/vue3";
import { getImageUrl } from "@/plugins/helpers";
const props = defineProps({
    item: {
        type: Object as () => CartItemModel,
        required: true,
    },
    showDivider: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits([
    "subtract",
    "add",
    "updateQuantity",
    "remove",
    "toggleItem",
]);

const slots = useSlots();
const hasActions = computed(() => {
    return !!slots.actions;
});
</script>

<template>
    <div
        class="flex flex-col items-center justify-between w-full gap-4 py-4 border-b border-gray-300"
        :class="{
            'border-none': !props.showDivider,
        }"
    >
        <div class="flex items-start w-full gap-x-4">
            <div class="flex items-center justify-center shrink-0 gap-4">
                <!-- Checkbox -->
                <Checkbox
                    :checked="props.item.selected"
                    class="transition-colors duration-200 ease-in-out border-2 border-gray-300 rounded-md cursor-pointer size-5 hover:bg-secondary hover:border-primary"
                    :class="{
                        'bg-primary!': props.item.selected,
                        'border-primary!': props.item.selected,
                    }"
                    @update:checked="emit('toggleItem')"
                />

                <!-- Image -->
                <img
                    v-if="props.item.image"
                    :src="getImageUrl(props.item.image)"
                    alt="Product Image"
                    class="object-cover size-[60px] sm:size-20 lg:size-[120px] rounded-lg outline-1 outline-gray-300"
                />
                <div
                    v-else
                    class="flex items-center justify-center size-[60px] sm:size-20 lg:size-[120px] bg-gray-100 rounded-lg aspect-square"
                >
                    <div
                        class="flex flex-col items-center justify-center gap-2"
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
                            class="lucide lucide-book-open size-6 lg:size-8 text-muted-foreground/50"
                        >
                            <path d="M12 7v14"></path>
                            <path
                                d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"
                            ></path>
                        </svg>
                        <span
                            class="hidden lg:block text-sm text-muted-foreground"
                            >No Cover</span
                        >
                    </div>
                </div>
            </div>

            <div
                v-if="props.item.book"
                class="flex flex-col items-center justify-between w-full gap-4 xl:flex-row"
            >
                <!-- Detail -->
                <div class="w-full">
                    <Link
                        :href="`/book/${props.item.book.slug}`"
                        class="w-full"
                    >
                        <h3
                            class="text-base font-semibold text-gray-800 sm:text-lg hover:underline"
                        >
                            {{ props.item.book.title }}
                        </h3>
                    </Link>

                    <!-- Price -->
                    <div class="flex flex-col gap-2 mt-1">
                        <div
                            class="text-xs sm:text-sm font-semibold text-gray-900"
                        >
                            {{ $formatCurrency(props.item.book.final_price) }}
                        </div>
                        <div
                            v-if="props.item.book.discount"
                            class="flex gap-2 items-center"
                        >
                            <div
                                class="text-xs font-medium text-red-600 bg-red-100 px-2 py-1 rounded-md"
                            >
                                {{ props.item.book.discount }}%
                            </div>
                            <span
                                class="text-xs sm:text-sm font-semibold text-gray-400 line-through"
                            >
                                {{
                                    $formatCurrency(props.item.book.slice_price)
                                }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div v-if="hasActions" class="hidden w-full sm:block xl:w-fit">
                    <slot name="actions" />
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div v-if="hasActions" class="block w-full sm:hidden">
            <slot name="actions" />
        </div>
    </div>
</template>
