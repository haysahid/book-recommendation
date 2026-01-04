<script setup lang="ts">
const props = defineProps({
    label: {
        type: String,
        required: false,
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
        class="text-xs text-gray-700 bg-gray-100 rounded-lg px-2 py-1"
        :class="isActive ? 'bg-blue-100!' : ''"
    >
        <span v-if="label" class="">{{ label }}:</span>
        {{ value }}
    </div>
</template>
