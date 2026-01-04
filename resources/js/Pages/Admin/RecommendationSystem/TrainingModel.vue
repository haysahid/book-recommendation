<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import TrainingModelForm from "./TrainingModelForm.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import ModelHistory from "./ModelHistory.vue";
import { useTrainingStore } from "@/stores/training-store";
import TrainingResult from "./TrainingResult.vue";

const trainingStore = useTrainingStore();
</script>

<template>
    <AdminLayout
        title="Training Model"
        :showTitle="true"
        :breadcrumbs="[
            {
                text: 'Recommendation System',
                url: '/admin/recommendation-system',
            },
            {
                text: 'Training Model',
                active: true,
            },
        ]"
    >
        <div class="p-2 sm:p-0 flex flex-col gap-2 sm:gap-6">
            <DefaultCard class="w-full">
                <TrainingModelForm
                    :previousModel="trainingStore?.models[1] ?? null"
                    @modelTrained="
                        async (model: ModelEntity) => {
                            await trainingStore.getModelHistory();
                            await trainingStore?.getActiveModel();
                        }
                    "
                />
            </DefaultCard>
            <DefaultCard class="w-full">
                <ModelHistory />
            </DefaultCard>
        </div>
    </AdminLayout>
</template>
