<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import TrainingModelForm from "./TrainingModelForm.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import ModelHistory from "./ModelHistory.vue";
import { ref } from "vue";

const resultModel = ref<ModelEntity | null>(null);

const modelHistory = ref(null);
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
        <div class="p-4 flex flex-col gap-6">
            <DefaultCard class="w-full">
                <TrainingModelForm
                    :previousModel="modelHistory?.models[1] ?? null"
                    @modelTrained="
                        async (model: ModelEntity) => {
                            resultModel = model;
                            await modelHistory?.getModelHistory();
                            await modelHistory?.getActiveModel();
                        }
                    "
                />
            </DefaultCard>
            <DefaultCard class="w-full">
                <ModelHistory ref="modelHistory" />
            </DefaultCard>
        </div>
    </AdminLayout>
</template>
