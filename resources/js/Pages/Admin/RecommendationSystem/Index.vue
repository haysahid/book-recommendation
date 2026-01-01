<script setup lang="ts">
import DefaultCard from "@/Components/DefaultCard.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import TrainModelForm from "./TrainModelForm.vue";
import ModelHistory from "./ModelHistory.vue";
import { ref } from "vue";

const resultModel = ref<ModelEntity | null>(null);

const modelHistory = ref(null);
</script>

<template>
    <AdminLayout title="Recommendation System" :showTitle="true">
        <div class="p-4 flex flex-col gap-6">
            <DefaultCard class="w-full">
                <TrainModelForm
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
