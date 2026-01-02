<script setup lang="ts">
const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    isHigher: {
        type: Boolean,
        required: false,
        default: false,
    },
    isLower: {
        type: Boolean,
        required: false,
        default: false,
    },
});
</script>

<template>
    <div
        class="px-4 py-2 h-full min-w-20 flex flex-col justify-center items-center xl:items-start"
    >
        <p
            class="text-xs text-gray-700 inline-flex items-center whitespace-nowrap"
        >
            {{ label }}
            <span v-if="isHigher" class="text-green-600 ml-1">▲</span>
            <span v-else-if="isLower" class="text-red-600 ml-1">▼</span>
        </p>
        <p class="font-semibold text-lg">
            {{ value }}
        </p>
    </div>
</template>
