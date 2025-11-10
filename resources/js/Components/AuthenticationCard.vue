<script setup lang="ts"></script>

<template>
    <div
        class="relative flex flex-col items-center justify-center px-6 py-6 overflow-hidden bg-gradient-hero h-screen"
    >
        <div
            data-aos="fade-up"
            data-aos-duration="300"
            class="z-10 w-full max-w-xs px-8 py-8 bg-white shadow-md rounded-2xl"
        >
            <div class="w-full mx-auto">
                <slot name="logo" />
            </div>
            <slot />
        </div>
    </div>
</template>
