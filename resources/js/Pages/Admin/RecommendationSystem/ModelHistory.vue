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
        <div class="flex gap-4 items-center justify-between">
            <h3 class="text-lg font-semibold">
                Training Model History
                <span v-if="trainingStore.models">
                    ({{ trainingStore.models?.length ?? 0 }})
                </span>
            </h3>

            <button
                v-if="trainingStore.getModelHistoryStatus == 'loading'"
                type="button"
                disabled
                class="flex items-center p-2.5"
            >
                <div class="circular-loading-xs"></div>
            </button>
            <button
                v-else
                type="button"
                @click="
                    trainingStore.getModelHistory();
                    trainingStore.getActiveModel();
                "
                class="p-2 rounded-md hover:bg-gray-100 active:bg-gray-200 transition"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    class="text-gray-600"
                >
                    <path
                        d="M12 20C9.76667 20 7.875 19.225 6.325 17.675C4.775 16.125 4 14.2333 4 12C4 9.76667 4.775 7.875 6.325 6.325C7.875 4.775 9.76667 4 12 4C13.15 4 14.25 4.23734 15.3 4.712C16.35 5.18667 17.25 5.866 18 6.75V5C18 4.71667 18.096 4.47934 18.288 4.288C18.48 4.09667 18.7173 4.00067 19 4C19.2827 3.99934 19.5203 4.09534 19.713 4.288C19.9057 4.48067 20.0013 4.718 20 5V10C20 10.2833 19.904 10.521 19.712 10.713C19.52 10.905 19.2827 11.0007 19 11H14C13.7167 11 13.4793 10.904 13.288 10.712C13.0967 10.52 13.0007 10.2827 13 10C12.9993 9.71734 13.0953 9.48 13.288 9.288C13.4807 9.096 13.718 9 14 9H17.2C16.6667 8.06667 15.9377 7.33334 15.013 6.8C14.0883 6.26667 13.084 6 12 6C10.3333 6 8.91667 6.58334 7.75 7.75C6.58333 8.91667 6 10.3333 6 12C6 13.6667 6.58333 15.0833 7.75 16.25C8.91667 17.4167 10.3333 18 12 18C13.1333 18 14.171 17.7127 15.113 17.138C16.055 16.5633 16.784 15.7923 17.3 14.825C17.4333 14.5917 17.621 14.4293 17.863 14.338C18.105 14.2467 18.3507 14.2423 18.6 14.325C18.8667 14.4083 19.0583 14.5833 19.175 14.85C19.2917 15.1167 19.2833 15.3667 19.15 15.6C18.4667 16.9333 17.4917 18 16.225 18.8C14.9583 19.6 13.55 20 12 20Z"
                        fill="currentColor"
                    />
                </svg>
            </button>
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
