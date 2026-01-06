<script setup lang="ts">
import ModelCard from "./ModelCard.vue";
import ThreeDotsLoading from "@/Components/ThreeDotsLoading.vue";
import { useTrainingStore } from "@/stores/training-store";

const trainingStore = useTrainingStore();
trainingStore.getModelHistory();
trainingStore.getActiveModel();
</script>

<template>
    <div>
        <div>
            <h3 class="text-lg font-semibold">
                Training Model History
                <span v-if="trainingStore.models">
                    ({{ trainingStore.models?.length ?? 0 }})
                </span>
            </h3>
        </div>

        <!-- <div
            v-if="trainingStore.getModelHistoryStatus === 'loading'"
            class="flex items-center justify-center h-[10vh]"
        >
            <ThreeDotsLoading class="text-blue-500" />
        </div> -->

        <div
            v-if="trainingStore.getModelHistoryStatus === 'error'"
            class="flex items-center justify-center h-[10vh]"
        >
            <p class="text-sm text-center text-gray-500">
                Failed to load model history. Please try again later.
            </p>
        </div>

        <div class="w-full mt-2.5">
            <div
                v-if="trainingStore.models?.length"
                class="flex flex-col w-full gap-2"
            >
                <template
                    v-for="(model, index) in trainingStore.models"
                    :key="model.id"
                >
                    <ModelCard
                        :model="model"
                        :isActive="model.id === trainingStore.activeModel?.id"
                        @activate="trainingStore.activateModel"
                        @delete="trainingStore.deleteModel"
                    />
                </template>
            </div>
            <div v-else class="flex items-center justify-center h-[10vh] mb-6">
                <ThreeDotsLoading
                    v-if="trainingStore.getModelHistoryStatus === 'loading'"
                />
                <p v-else class="text-sm text-center text-gray-500">
                    No data found.
                </p>
            </div>
        </div>
    </div>
</template>
