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
    isActive: {
        type: Boolean,
        required: false,
        default: false,
    },
});
</script>

<template>
    <div
        class="text-sm text-gray-700 bg-gray-100 rounded-lg px-2 py-1"
        :class="isActive ? 'bg-blue-100! font-semibold' : ''"
    >
        <span class="font-medium">{{ label }}:</span>
        {{ value }}
    </div>
</template>
