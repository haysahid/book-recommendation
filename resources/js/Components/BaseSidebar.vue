<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import SidebarMenu from "./SidebarMenu.vue";
import SidebarMenuGroup from "./SidebarMenuGroup.vue";

const props = defineProps({
    menus: {
        type: Array as () => SidebarMenuModel[],
        default: () => [],
    },
    responsive: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <aside
        class="fixed top-0 left-0 w-0 h-screen overflow-y-hidden transition-all duration-300 ease-in-out bg-white md:w-64"
        :class="{
            'static! w-full': props.responsive,
        }"
    >
        <nav
            class="h-full"
            :class="{
                'py-4!': props.responsive,
            }"
        >
            <!-- Logo -->
            <div
                v-if="!props.responsive"
                class="flex items-center px-6 py-2.5 mb-1"
            >
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
                            class="size-6 text-primary"
                        >
                            <path d="M12 7v14"></path>
                            <path
                                d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"
                            ></path>
                        </svg>
                        <span class="text-lg font-semibold"> Book Reco </span>
                    </div>
                </Link>
            </div>

            <div
                class="overflow-y-auto h-[calc(100vh-72px)]"
                :class="{
                    'h-[calc(100vh-178px)]': props.responsive,
                }"
            >
                <slot name="extraMenu" />
                <ul>
                    <li v-for="menu in props.menus" :key="menu.name">
                        <SidebarMenuGroup
                            v-if="menu.children"
                            :menu="menu"
                            :responsive="props.responsive"
                        />

                        <SidebarMenu
                            v-else
                            :menu="menu"
                            :responsive="props.responsive"
                        />
                    </li>
                </ul>
            </div>
        </nav>
    </aside>
</template>
