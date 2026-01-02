<script setup lang="ts">
import cookieManager from "@/plugins/cookie-manager";
import axios from "axios";
import { ref } from "vue";
import ModelCard from "./ModelCard.vue";
import ThreeDotsLoading from "@/Components/ThreeDotsLoading.vue";
import { useDialogStore } from "@/stores/dialog-store";

const models = ref<ModelEntity[]>([]);
const getModelHistoryStatus = ref(null);

async function getModelHistory() {
    getModelHistoryStatus.value = "loading";
    try {
        await axios
            .get("/api/admin/recommendation-system/model-history", {
                headers: {
                    Authorization: `Bearer ${cookieManager.getItem(
                        "access_token"
                    )}`,
                },
            })
            .then((response) => {
                models.value = response.data.result;
                getModelHistoryStatus.value = "success";
            })
            .catch((err) => {
                getModelHistoryStatus.value = "error";
            });
    } catch (err) {
        getModelHistoryStatus.value = "error";
    }
}
getModelHistory();

const activeModel = ref<ModelEntity | null>(null);

async function getActiveModel() {
    try {
        await axios
            .get("/api/admin/recommendation-system/active-model", {
                headers: {
                    Authorization: `Bearer ${cookieManager.getItem(
                        "access_token"
                    )}`,
                },
            })
            .then((response) => {
                activeModel.value = response.data.result.model;
            })
            .catch((err) => {
                activeModel.value = null;
            });
        return activeModel.value;
    } catch (err) {
        return null;
    }
}
getActiveModel();

async function activateModel(modelId: number) {
    try {
        await axios
            .post(
                `/api/admin/recommendation-system/activate-model/${modelId}`,
                {},
                {
                    headers: {
                        Authorization: `Bearer ${cookieManager.getItem(
                            "access_token"
                        )}`,
                    },
                }
            )
            .then((response) => {
                getActiveModel();
            })
            .catch((err) => {
                useDialogStore().openErrorDialog(
                    "An error occurred while activating the model."
                );
            });
    } catch (err) {
        useDialogStore().openErrorDialog(
            "An error occurred while activating the model."
        );
    }
}

async function deleteModel(modelId: number) {
    try {
        await axios
            .delete(
                `/api/admin/recommendation-system/model-history/${modelId}`,
                {
                    headers: {
                        Authorization: `Bearer ${cookieManager.getItem(
                            "access_token"
                        )}`,
                    },
                }
            )
            .then(async (response) => {
                await getModelHistory();
                await getActiveModel();
                useDialogStore().openSuccessDialog(
                    "Model deleted successfully."
                );
            })
            .catch((err) => {
                useDialogStore().openErrorDialog(
                    "An error occurred while deleting the model."
                );
            });
    } catch (err) {
        useDialogStore().openErrorDialog(
            "An error occurred while deleting the model."
        );
    }
}

defineExpose({
    models,
    getModelHistory,
    getActiveModel,
    activateModel,
});
</script>

<template>
    <div>
        <div>
            <h3 class="text-lg font-semibold">Training Model History</h3>
        </div>

        <div v-if="getModelHistoryStatus === 'loading'" class="text-gray-600">
            Loading model history...
        </div>

        <div v-else-if="getModelHistoryStatus === 'error'" class="text-red-600">
            Failed to load model history. Please try again later.
        </div>

        <div v-else class="w-full mt-2.5">
            <div v-if="models?.length" class="flex flex-col w-full gap-2">
                <ModelCard
                    v-for="(model, index) in models"
                    :key="model.id"
                    :model="model"
                    :isActive="model.id === activeModel?.id"
                    @activate="activateModel"
                    @delete="deleteModel"
                />
            </div>
            <div v-else class="flex items-center justify-center h-[10vh] mb-6">
                <ThreeDotsLoading v-if="getModelHistoryStatus === 'loading'" />
                <p v-else class="text-sm text-center text-gray-500">
                    No data found.
                </p>
            </div>
        </div>
    </div>
</template>
