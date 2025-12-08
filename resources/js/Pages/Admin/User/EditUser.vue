<script setup>
import DefaultCard from "@/Components/DefaultCard.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import UserForm from "./UserForm.vue";

const props = defineProps({
    user: Object,
});
</script>

<template>
    <AdminLayout
        title="Edit User"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Users', url: '/admin/user', active: false },
            { text: 'Edit User', active: true },
        ]"
    >
        <DefaultCard>
            <UserForm :user="user" />
        </DefaultCard>
    </AdminLayout>
</template>
