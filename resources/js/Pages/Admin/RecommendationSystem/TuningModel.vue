<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import TuningModelForm from "./TuningModelForm.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
</script>

<template>
    <AdminLayout
        title="Tuning Model"
        :showTitle="true"
        :breadcrumbs="[
            {
                text: 'Recommendation System',
                url: '/admin/recommendation-system',
            },
            {
                text: 'Tuning Model',
                url: '/admin/recommendation-system/tuning-model',
            },
        ]"
    >
        <div class="p-4">
            <DefaultCard class="w-full">
                <TuningModelForm />
            </DefaultCard>
        </div>
    </AdminLayout>
</template>
