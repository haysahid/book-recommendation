<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import TuningModelForm from "./TuningModelForm.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import { useTrainingStore } from "@/stores/training-store";
import TrainingResult from "./TrainingResult.vue";
import ModelStats from "./ModelStats.vue";

const trainingStore = useTrainingStore();
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
                active: true,
            },
        ]"
    >
        <div class="p-2 sm:p-0 flex flex-col gap-4">
            <DefaultCard class="w-full">
                <TuningModelForm />
            </DefaultCard>
        </div>
    </AdminLayout>
</template>
