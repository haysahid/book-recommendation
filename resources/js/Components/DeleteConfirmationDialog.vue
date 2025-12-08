<script setup>
import DialogModal from "@/Components/DialogModal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    title: {
        type: String,
        default: "Are you sure you want to delete this data?",
    },
    description: {
        type: String,
        default: null,
    },
    positiveButtonText: {
        type: String,
        default: "Confirm",
    },
    negativeButtonText: {
        type: String,
        default: "Cancel",
    },
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["close", "delete"]);

const close = () => {
    emit("close");
};
</script>
<template>
    <DialogModal :show="props.show" @close="close" maxWidth="sm">
        <template #icon>
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                class="size-16 text-red-500"
            >
                <path
                    d="M17 2.42969H7C4 2.42969 2 4.42969 2 7.42969V13.4297C2 16.4297 4 18.4297 7 18.4297V20.5597C7 21.3597 7.89 21.8397 8.55 21.3897L13 18.4297H17C20 18.4297 22 16.4297 22 13.4297V7.42969C22 4.42969 20 2.42969 17 2.42969ZM12 14.5997C11.58 14.5997 11.25 14.2597 11.25 13.8497C11.25 13.4397 11.58 13.0997 12 13.0997C12.42 13.0997 12.75 13.4397 12.75 13.8497C12.75 14.2597 12.42 14.5997 12 14.5997ZM13.26 10.4497C12.87 10.7097 12.75 10.8797 12.75 11.1597V11.3697C12.75 11.7797 12.41 12.1197 12 12.1197C11.59 12.1197 11.25 11.7797 11.25 11.3697V11.1597C11.25 9.99969 12.1 9.42969 12.42 9.20969C12.79 8.95969 12.91 8.78969 12.91 8.52969C12.91 8.02969 12.5 7.61969 12 7.61969C11.5 7.61969 11.09 8.02969 11.09 8.52969C11.09 8.93969 10.75 9.27969 10.34 9.27969C9.93 9.27969 9.59 8.93969 9.59 8.52969C9.59 7.19969 10.67 6.11969 12 6.11969C13.33 6.11969 14.41 7.19969 14.41 8.52969C14.41 9.66969 13.57 10.2397 13.26 10.4497Z"
                    fill="currentColor"
                />
            </svg>
        </template>
        <template #title>
            <h3
                v-html="props.title"
                class="text-lg font-medium leading-6 text-gray-900 text-wrap"
            ></h3>
        </template>
        <template v-if="props.description" #content>
            <p class="mb-1.5 text-center text-wrap">
                {{ props.description }}
            </p>
        </template>
        <slot />
        <template #footer>
            <div class="flex gap-4 text-base">
                <SecondaryButton type="button" class="" @click="emit('close')">
                    Cancel
                </SecondaryButton>
                <PrimaryButton
                    type="button"
                    class="bg-red-500 hover:bg-red-500/80 active:bg-red-500/90 focus:bg-red-500 focus:ring-red-500"
                    @click="emit('delete')"
                >
                    {{ props.positiveButtonText }}
                </PrimaryButton>
            </div>
        </template>
    </DialogModal>
</template>
