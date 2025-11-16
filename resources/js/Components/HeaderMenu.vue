<script setup lang="ts">
import NavLink from "@/Components/NavLink.vue";

const props = defineProps({
    menu: {
        type: Object as () => SidebarMenuModel,
        required: true,
    },
    invertColor: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <NavLink
        :key="props.menu.name"
        :href="props.menu.href"
        :active="props.menu.active"
        class="text-sm font-normal transition-all duration-300 ease-in-out"
        :class="
            props.invertColor
                ? {
                      'text-secondary-light!': props.menu.active,
                      'text-white/80! hover:text-white/70!': !props.menu.active,
                  }
                : {
                      'text-secondary!': props.menu.active,
                      'text-gray-500! hover:text-gray-600!': !props.menu.active,
                  }
        "
    >
        {{ props.menu.name }}
    </NavLink>
</template>
