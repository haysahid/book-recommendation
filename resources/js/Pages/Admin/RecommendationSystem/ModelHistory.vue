<script setup lang="ts">
import cookieManager from "@/plugins/cookie-manager";
import axios from "axios";
import { ref } from "vue";
import ModelCard from "./ModelCard.vue";
import ThreeDotsLoading from "@/Components/ThreeDotsLoading.vue";
import { useDialogStore } from "@/stores/dialog-store";
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

        <div
            v-if="trainingStore.getModelHistoryStatus === 'loading'"
            class="text-gray-600"
        >
            Loading model history...
        </div>

        <div
            v-else-if="trainingStore.getModelHistoryStatus === 'error'"
            class="text-red-600"
        >
            Failed to load model history. Please try again later.
        </div>

        <div v-else class="w-full mt-2.5">
            <div
                v-if="trainingStore.models?.length"
                class="flex flex-col w-full gap-2"
            >
                <ModelCard
                    v-for="(model, index) in trainingStore.models"
                    :key="model.id"
                    :model="model"
                    :isActive="model.id === trainingStore.activeModel?.id"
                    @activate="trainingStore.activateModel"
                    @delete="trainingStore.deleteModel"
                />
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
