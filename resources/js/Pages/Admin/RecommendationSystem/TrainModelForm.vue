<script setup lang="ts">
import InfoTooltip from "@/Components/InfoTooltip.vue";
import InputGroup from "@/Components/InputGroup.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import cookieManager from "@/plugins/cookie-manager";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";
import { ref } from "vue";

const emit = defineEmits(["modelTrained"]);

const form = useForm({
    books_file: null,
    transactions_file: null,
    n_factors: null,
    n_epochs: null,
    lr_all: null,
    reg_all: null,
});

const algorithms = [
    "Collaborative Filtering",
    "Content-Based",
    "Matrix Factorization",
];

const trainModelStatus = ref(null);
const model = ref<ModelEntity | null>(null);
const error = ref(null);

const startTraining = async () => {
    trainModelStatus.value = "loading";
    model.value = null;
    error.value = null;

    try {
        await axios
            .post("/api/admin/recommendation-system/train", form, {
                headers: {
                    "Content-Type": "multipart/form-data",
                    Authorization: `Bearer ${cookieManager.getItem(
                        "access_token"
                    )}`,
                },
            })
            .then((response) => {
                model.value = response.data.result.model;
                trainModelStatus.value = "success";
                emit("modelTrained", model.value);
            })
            .catch((err) => {
                if (err.response && err.response.data.meta) {
                    error.value =
                        err.response.data.meta.message || "Training failed.";
                } else {
                    error.value = "Training failed.";
                }
                trainModelStatus.value = "error";
            });
    } catch (err) {
        error.value = "An unexpected error occurred.";
        trainModelStatus.value = "error";
    }
};
</script>

<style scoped>
.container {
    max-width: 600px;
}
</style>

<template>
    <div>
        <div class="flex gap-2.5 items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold">Train Model</h3>
                <p class="text-sm text-gray-500">
                    Train the recommendation system model with new data.
                </p>
            </div>
            <PrimaryButton
                type="submit"
                :disabled="trainModelStatus == 'loading'"
                class="whitespace-nowrap"
                @click="startTraining"
            >
                {{
                    trainModelStatus == "loading"
                        ? "Training..."
                        : "Start Training"
                }}
            </PrimaryButton>
        </div>

        <form @submit.prevent="startTraining" class="mt-4">
            <div class="flex flex-col w-full gap-4 lg:flex-row lg:gap-6">
                <div class="flex flex-col w-full gap-4">
                    <!-- Books File -->
                    <InputGroup id="books_file" label="Books File">
                        <input
                            id="books_file"
                            type="file"
                            @change="
                                (e) =>
                                    (form.books_file =
                                        e.target.files[0] || null)
                            "
                            class="border border-gray-300 rounded p-2 w-full"
                        />
                        <div
                            v-if="form.errors.books_file"
                            class="text-red-600 text-sm mt-1"
                        >
                            {{ form.errors.books_file }}
                        </div>
                    </InputGroup>

                    <!-- Transactions File -->
                    <InputGroup
                        id="transactions_file"
                        label="Transactions File"
                    >
                        <input
                            id="transactions_file"
                            type="file"
                            @change="
                                (e) =>
                                    (form.transactions_file =
                                        e.target.files[0] || null)
                            "
                            class="border border-gray-300 rounded p-2 w-full"
                        />
                        <div
                            v-if="form.errors.transactions_file"
                            class="text-red-600 text-sm mt-1"
                        >
                            {{ form.errors.transactions_file }}
                        </div>
                    </InputGroup>
                </div>

                <div class="flex flex-col w-full gap-4">
                    <div class="flex gap-4">
                        <!-- Number of Factors-->
                        <InputGroup id="n_factors" label="Number of Factors">
                            <TextInput
                                id="n_factors"
                                v-model="form.n_factors"
                                type="number"
                                placeholder="100"
                                :error="form.errors.n_factors"
                                @update:modelValue="
                                    form.errors.n_factors = null
                                "
                            />
                            <template #suffix>
                                <InfoTooltip
                                    id="n-factors-info"
                                    text="Number of latent factors used in the model."
                                />
                            </template>
                        </InputGroup>

                        <!-- Number of Epochs -->
                        <InputGroup id="n_epochs" label="Number of Epochs">
                            <TextInput
                                id="n_epochs"
                                v-model="form.n_epochs"
                                type="number"
                                placeholder="20"
                                :error="form.errors.n_epochs"
                                @update:modelValue="form.errors.n_epochs = null"
                            />
                            <template #suffix>
                                <InfoTooltip
                                    id="n-epochs-info"
                                    text="Number of training iterations."
                                />
                            </template>
                        </InputGroup>
                    </div>

                    <div class="flex gap-4">
                        <!-- Learning Rate -->
                        <InputGroup id="lr_all" label="Learning Rate">
                            <TextInput
                                id="lr_all"
                                v-model="form.lr_all"
                                type="number"
                                step="0.0001"
                                placeholder="0.005"
                                :error="form.errors.lr_all"
                                @update:modelValue="form.errors.lr_all = null"
                            />
                            <template #suffix>
                                <InfoTooltip
                                    id="lr-all-info"
                                    text="Learning rate for the training algorithm."
                                />
                            </template>
                        </InputGroup>

                        <!-- Regularization Term -->
                        <InputGroup id="reg_all" label="Regularization Term">
                            <TextInput
                                id="reg_all"
                                v-model="form.reg_all"
                                type="number"
                                step="0.0001"
                                placeholder="0.02"
                                :error="form.errors.reg_all"
                                @update:modelValue="form.errors.reg_all = null"
                            />
                            <template #suffix>
                                <InfoTooltip
                                    id="reg-all-info"
                                    text="Regularization term to prevent overfitting."
                                />
                            </template>
                        </InputGroup>
                    </div>
                </div>
            </div>
        </form>
        <div v-if="model" class="mt-6 bg-green-100 text-green-800 p-4 rounded">
            <h2 class="font-semibold mb-2">Training Result</h2>
            <pre class="whitespace-pre-wrap">{{ model }}</pre>
        </div>
        <div v-if="error" class="mt-6 bg-red-100 text-red-800 p-4 rounded">
            <h2 class="font-semibold mb-2">Error</h2>
            <pre class="whitespace-pre-wrap">{{ error }}</pre>
        </div>
    </div>
</template>
