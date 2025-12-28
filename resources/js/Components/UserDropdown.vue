<script setup lang="ts">
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    invert: {
        type: Boolean,
        default: false,
    },
    responsive: {
        type: Boolean,
        default: false,
    },
});

const logout = () => {
    router.post("/logout");
};
</script>

<template>
    <Dropdown align="right" width="48">
        <template #trigger>
            <div
                class="flex rounded-md"
                :class="{
                    'px-1': props.responsive,
                }"
            >
                <button
                    type="button"
                    class="flex items-center justify-start w-full gap-2 px-4 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-transparent border border-transparent rounded-md md:px-3 text-start md:w-auto focus:outline-none hover:bg-gray-500/10 focus:bg-gray-500/10 active:bg-gray-500/20 group"
                    :class="{
                        'text-white/80! hover:bg-white/10 focus:bg-white/10':
                            props.invert,
                    }"
                >
                    <img
                        class="object-cover rounded-full size-8 shrink-0"
                        :src="
                            $getImageUrl(
                                $page.props.auth.user?.profile_photo_path
                            ) ?? $page.props.auth.user?.profile_photo_url
                        "
                        :alt="$page.props.auth.user?.name"
                    />

                    <div class="w-full sm:w-auto sm:hidden md:inline">
                        <span>{{ $page.props.auth.user?.name }}</span>
                    </div>

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="size-4 sm:hidden md:inline shrink-0"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                        />
                    </svg>
                </button>
            </div>
        </template>

        <template #content>
            <div class="divide-y divide-gray-200">
                <DropdownLink
                    v-if="
                        $page.props.auth.user.roles
                            .map((role) => role.name)
                            .includes('Super Admin') ||
                        $page.props.auth.user.roles
                            .map((role) => role.name)
                            .includes('Admin')
                    "
                    :href="'/admin/scraping'"
                >
                    Dashboard
                </DropdownLink>

                <DropdownLink as="button" @click="logout">
                    Log Out
                </DropdownLink>
            </div>
        </template>
    </Dropdown>
</template>
