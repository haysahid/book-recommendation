<script setup lang="ts">
import RatingSmall from "./RatingSmall.vue";

const props = defineProps({
    book: Object as () => BookEntity,
    reviews: Array as () => BookReviewEntity[],
});
</script>

<template>
    <div
        class="flex flex-col xl:flex-row gap-x-8 gap-y-4 items-start xl:items-center"
    >
        <div class="whitespace-nowrap">
            <div class="flex items-center gap-3">
                <span class="text-4xl font-bold flex items-center gap-2">
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
                        class="lucide lucide-star size-8 text-yellow-400 fill-current"
                    >
                        <path
                            d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"
                        ></path>
                    </svg>
                    {{ props.book.average_rating.toFixed(1) }}
                </span>
                <span class="text-2xl text-gray-400">/ 5.0</span>
            </div>
            <div class="mt-1 text-base">
                {{
                    props.book.average_rating > 0
                        ? Math.round((props.book.average_rating / 5) * 100)
                        : 0
                }}% of buyers are satisfied
            </div>
            <div class="text-gray-500 mt-1 text-sm">
                {{ props.book.rating_count }} ratings &bull;
                {{ props.reviews.length }} reviews
            </div>
        </div>
        <div class="w-full space-y-0.5">
            <div
                v-for="star in [5, 4, 3, 2, 1]"
                :key="star"
                class="flex items-center gap-2"
            >
                <RatingSmall
                    :rating="star"
                    :showRatingCount="false"
                    starClass="size-3!"
                />
                <div
                    class="flex-1 bg-gray-200 rounded h-1.5 relative overflow-hidden"
                >
                    <div
                        class="bg-green-600 h-2 rounded"
                        :style="{
                            width:
                                props.book.rating_count > 0
                                    ? (
                                          (props.reviews.filter(
                                              (review) =>
                                                  Math.round(review.rating) ===
                                                  star
                                          ).length /
                                              props.book.rating_count) *
                                          100
                                      ).toFixed(2) + '%'
                                    : '0%',
                        }"
                    ></div>
                </div>
                <span class="w-8 text-left text-gray-500 text-sm">
                    {{
                        props.reviews.filter(
                            (review) => Math.round(review.rating) === star
                        ).length ?? 0
                    }}
                </span>
            </div>
        </div>
    </div>
</template>
