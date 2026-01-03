<script setup lang="ts">
import RatingSmall from "./RatingSmall.vue";

const props = defineProps({
    review: {
        type: Object as () => BookReviewEntity,
        required: true,
    },
});
</script>

<template>
    <div class="flex items-start gap-3">
        <img
            :src="
                $getImageUrl(
                    props.review.user.profile_photo_path ??
                        props.review.user.profile_photo_url
                )
            "
            alt="Foto Pengguna"
            class="object-cover rounded-full size-12 h-fit shrink-0 aspect-square"
        />
        <div class="w-full">
            <div class="flex gap-4 justify-between items-center">
                <div class="font-medium">
                    {{ props.review.user.name || "Unknown" }}
                </div>
                <div class="text-sm text-gray-500">
                    {{ $formatDate(props.review.reviewed_at) }}
                </div>
            </div>
            <RatingSmall
                :rating="props.review.rating"
                :showRatingCount="false"
                class="mt-0.5"
            />
            <p v-if="props.review.review" class="text-sm text-gray-700 mt-1">
                {{ props.review.review }}
            </p>
        </div>
    </div>
</template>
