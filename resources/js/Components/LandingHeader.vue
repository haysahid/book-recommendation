<script setup lang="ts">
import { computed, ref, onMounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import UserDropdown from "./UserDropdown.vue";
import HamburgerButton from "./HamburgerButton.vue";
import CustomPageProps from "@/types/model/CustomPageProps";
import HeaderResponsiveMenu from "./HeaderResponsiveMenu.vue";
import HeaderMenu from "./HeaderMenu.vue";

const props = defineProps({
    invertColor: {
        type: Boolean,
        required: false,
        default: false,
    },
});

const showingNavigationDropdown = ref(false);

let menus = [
    {
        name: "Search",
        href: "/book",
        active: false,
    },
];

const page = usePage<CustomPageProps>();

if (window.location.pathname !== "/login" && !page.props.auth.user) {
    menus.push({
        name: "Login",
        href: "/login",
        active: false,
    });
}

const invertColor = computed(() => {
    return props.invertColor;
});
</script>

<template>
    <nav
        class="sticky top-0 z-50 py-1 bg-white sm:px-12 lg:px-[100px] w-full transition-all duration-300 ease-in-out border-b border-gray-100"
        :class="{
            'border-b border-primary bg-linear-to-r from-primary to-accent from-0% to-240%':
                invertColor,
        }"
    >
        <!-- Primary Navigation Menu -->
        <div class="px-6 mx-auto max-w-7xl sm:px-0">
            <div class="flex justify-between h-16">
                <div class="flex justify-between w-full">
                    <!-- Logo -->
                    <div class="flex items-center shrink-0">
                        <Link href="/" class="shrink-0">
                            <div class="flex items-center gap-3 h-12">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="size-6 transition-all duration-300 ease-in-out"
                                    :class="{
                                        'text-white': invertColor,
                                        'text-primary': !invertColor,
                                    }"
                                >
                                    <path d="M12 7v14"></path>
                                    <path
                                        d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"
                                    ></path>
                                </svg>
                                <span
                                    class="text-lg font-semibold transition-all duration-300 ease-in-out"
                                    :class="{
                                        'text-white': invertColor,
                                        'text-primary': !invertColor,
                                    }"
                                >
                                    Book Reco
                                </span>
                            </div>
                        </Link>
                    </div>

                    <div class="flex items-center gap-4 sm:gap-6">
                        <!-- Navigation Links -->
                        <div
                            class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex sm:items-center"
                        >
                            <!-- Menus -->
                            <HeaderMenu
                                v-for="menu in menus"
                                :key="menu.name"
                                :menu="menu"
                                :invertColor="invertColor"
                            />
                        </div>

                        <!-- Divider -->
                        <!-- <span
                            class="hidden w-px h-6 bg-gray-300 sm:inline-block"
                        ></span> -->

                        <div class="flex items-center gap-2">
                            <!-- Trailing Menus -->
                            <div class="hidden gap-x-8 sm:flex sm:items-center">
                                <!-- Settings Dropdown -->
                                <div
                                    v-if="$page.props.auth.user"
                                    class="relative"
                                >
                                    <UserDropdown :invert="invertColor" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hamburger -->
                <div class="flex items-center -me-2 sm:hidden">
                    <HamburgerButton
                        :invert="invertColor"
                        :active="showingNavigationDropdown"
                        @toggle="
                            showingNavigationDropdown =
                                !showingNavigationDropdown
                        "
                    />
                </div>
            </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div
            :class="{
                block: showingNavigationDropdown,
                hidden: !showingNavigationDropdown,
            }"
            class="pb-2 sm:hidden"
        >
            <ul>
                <li v-for="menu in menus" :key="menu.name">
                    <HeaderResponsiveMenu
                        :menu="menu"
                        :invertColor="invertColor"
                    />
                </li>
            </ul>

            <div
                class="h-px mx-5 my-2 transition-all duration-300 ease-in-out bg-gray-300"
                :class="{
                    'bg-white/20': invertColor,
                }"
            ></div>

            <ul>
                <!-- Settings Dropdown -->
                <li v-if="$page.props.auth.user">
                    <div class="relative">
                        <UserDropdown :invert="invertColor" />
                    </div>
                </li>
            </ul>
        </div>
    </nav>
</template>
