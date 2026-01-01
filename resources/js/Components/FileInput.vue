<script setup lang="ts">
import InputError from "./InputError.vue";

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    file: {
        type: File,
        default: null,
    },
    error: {
        type: String,
        default: null,
    },
    errorClass: {
        type: String,
        default: "",
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    placeholder: {
        type: String,
        default: "Choose a file",
    },
});

const emit = defineEmits(["update:file"]);
</script>

<template>
    <div>
        <input
            :id="props.id"
            type="file"
            :disabled="props.disabled"
            @change="(e: Event) => {
                const target = e.target as HTMLInputElement;
                const file = target.files ? target.files[0] : null;
                emit('update:file', file);
            }"
            class="border border-gray-200 rounded-lg px-4 py-0 w-full shadow-xs focus:border-primary-light focus:ring-primary-light focus:outline-primary-light overflow-ellipsis placeholder-gray-400 file:px-4 file:py-2 file:text-sm file:font-normal file:text-gray-600 file:bg-gray-100 file:my-0 text-sm"
            :class="[
                props.file ? 'text-gray-900' : 'text-gray-400',
                {
                    'border-red-500': props.error,
                },
            ]"
            :placeholder="props.placeholder"
        />
        <InputError
            :message="props.error"
            class="px-2 mt-1"
            :class="[props.errorClass]"
        />
    </div>
</template>
