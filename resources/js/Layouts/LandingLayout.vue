<script setup>
import { Head } from "@inertiajs/vue3";
import LandingHeader from "@/Components/LandingHeader.vue";
import { ref, computed, onMounted } from "vue";
import { scrollToTop } from "@/plugins/helpers";

defineProps({
    title: String,
});

const scrolled = ref(false);
const scrollThreshold = 50;

const handleScroll = () => {
    scrolled.value = window.scrollY > scrollThreshold;
};

onMounted(() => {
    window.addEventListener("scroll", handleScroll);
});

const invertColor = computed(() => {
    return scrolled.value;
});
</script>

<template>
    <div>
        <Head :title="title" />

        <div class="relative w-full min-h-screen bg-white">
            <!-- Header -->
            <LandingHeader :invertColor="invertColor" />

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow">
                <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>

            <!-- Scroll to Top -->
            <transition name="accordion">
                <button
                    v-if="scrolled"
                    type="button"
                    @click="scrollToTop()"
                    class="fixed bottom-6 right-6 p-3 bg-primary text-white rounded-full shadow-lg hover:bg-primary-dark transition-colors duration-300 ease-in-out"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5 15l7-7 7 7"
                        />
                    </svg>
                </button>
            </transition>
        </div>
    </div>
</template>
