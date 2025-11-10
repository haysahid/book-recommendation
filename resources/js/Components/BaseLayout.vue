<script setup lang="ts">
import { Head, usePage } from "@inertiajs/vue3";
import SuccessDialog from "./SuccessDialog.vue";
import ErrorDialog from "./ErrorDialog.vue";
import { useDialogStore } from "@/stores/dialog-store";
import { onMounted } from "vue";
import CustomPageProps from "@/types/model/CustomPageProps";

defineProps({
    title: String,
});

const dialogStore = useDialogStore();

const page = usePage<CustomPageProps>();

onMounted(() => {
    if (page.props.flash?.success) {
        dialogStore.openSuccessDialog(page.props.flash?.success);
    } else if (page.props.flash?.error) {
        dialogStore.openErrorDialog(page.props.flash?.error);
    }
});
</script>

<template>
    <div>
        <Head :title="title" />

        <div class="flex h-screen bg-secondary-box">
            <slot name="sidebar" />

            <div
                id="main-area"
                class="flex-1 w-full transition-all duration-300 ease-in-out md:ml-64 h-[calc(100vh-72px)] sm:min-h-[calc(100vh-72px)] relative mt-[72px] overflow-x-hidden"
            >
                <slot name="header" />

                <main class="sm:p-6">
                    <slot />
                </main>
            </div>
        </div>

        <SuccessDialog
            :show="dialogStore.showSuccessDialog"
            :title="dialogStore.successMessage"
            @close="dialogStore.showSuccessDialog = false"
        />

        <ErrorDialog
            :show="dialogStore.showErrorDialog"
            @close="dialogStore.showErrorDialog = false"
        >
            <template #content>
                <div>
                    <div
                        class="mb-1 text-lg font-semibold text-center text-gray-900"
                    >
                        Terjadi Kesalahan
                    </div>
                    <p class="text-sm text-center text-gray-700">
                        {{ dialogStore.errorMessage }}
                    </p>
                </div>
            </template>
        </ErrorDialog>
    </div>
</template>
