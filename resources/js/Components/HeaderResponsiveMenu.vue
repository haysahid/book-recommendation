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
        :href="props.menu.href"
        :active="props.menu.active"
        class="px-4 py-2.5 w-full bg-transparent font-normal hover:bg-primary/5 hover:border-l-4 hover:border-primary/50 border-l-4 transition-all duration-300 ease-in-out"
        :class="
            props.invertColor
                ? {
                      '!text-secondary hover:!text-secondary !border-secondary':
                          props.menu.active,
                      '!text-white/80 hover:!text-white/80 hover:!bg-white/10 focus:!bg-white/10 border-transparent':
                          !props.menu.active,
                  }
                : {
                      '!text-secondary-dark hover:!text-secondary-dark !bg-secondary/10 !border-secondary':
                          props.menu.active,
                      '!text-gray-500 hover:!text-gray-600 ':
                          !props.menu.active,
                  }
        "
    >
        {{ props.menu.name }}
    </NavLink>
</template>
