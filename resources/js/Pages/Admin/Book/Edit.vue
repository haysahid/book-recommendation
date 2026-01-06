<script setup>
import DefaultCard from "@/Components/DefaultCard.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import BookForm from "./BookForm.vue";

const props = defineProps({
    book: Object,
    categories: Array,
});
</script>

<template>
    <AdminLayout
        title="Edit Book"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Books', url: '/admin/book', active: false },
            { text: 'Edit Book', active: true },
        ]"
    >
        <DefaultCard>
            <BookForm :book="book" :categories="categories" />
        </DefaultCard>
    </AdminLayout>
</template>
