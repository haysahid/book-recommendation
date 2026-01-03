<script setup lang="ts">
const props = defineProps({
    rating: {
        type: Number,
        required: true,
    },
    ratingCount: {
        type: Number,
        default: 0,
    },
    maxRating: {
        type: Number,
        default: 5,
    },
    starClass: {
        type: String,
        default: "",
    },
    showRatingCount: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <div class="flex items-center gap-0.5">
        <template v-for="index in props.maxRating" :key="index">
            <svg
                v-if="index <= Math.floor(props.rating)"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="lucide lucide-star size-4 text-yellow-400 fill-current"
                :class="props.starClass"
            >
                <path
                    d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"
                ></path>
            </svg>
            <svg
                v-else-if="
                    index === Math.ceil(props.rating) &&
                    !Number.isInteger(props.rating)
                "
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="lucide lucide-star-half size-4 text-yellow-400 fill-current"
                :class="props.starClass"
            >
                <path
                    d="M12 2 9.515 8.61l-7.29.562 5.565 5.42-1.348 7.29L12 18.896V2z"
                ></path>
                <path
                    d="M12 2v16.896l6.563 3.486-1.348-7.29 5.565-5.42-7.29-.562L12 2z"
                    class="fill-white"
                ></path>
            </svg>
            <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="lucide lucide-star size-4 text-gray-300"
                :class="props.starClass"
            >
                <path
                    d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"
                ></path>
            </svg>
        </template>

        <span v-if="props.showRatingCount" class="text-sm text-gray-500 ms-1">
            ({{ props.ratingCount }})
        </span>
    </div>
</template>
