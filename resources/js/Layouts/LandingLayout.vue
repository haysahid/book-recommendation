<script setup>
import { Head } from "@inertiajs/vue3";

defineProps({
    title: String,
});
</script>

<template>
    <div>
        <Head :title="title" />

        <div class="relative w-full min-h-screen bg-white">
            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
