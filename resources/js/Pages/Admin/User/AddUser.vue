<script setup>
import DefaultCard from "@/Components/DefaultCard.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import UserForm from "./UserForm.vue";
</script>

<template>
    <AdminLayout
        title="Add User"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Users', url: '/admin/user', active: false },
            { text: 'Add User', active: true },
        ]"
    >
        <DefaultCard>
            <UserForm />
        </DefaultCard>
    </AdminLayout>
</template>
