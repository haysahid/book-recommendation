<script setup lang="ts">
import InfoTooltip from "@/Components/InfoTooltip.vue";
import InputGroup from "@/Components/InputGroup.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import cookieManager from "@/plugins/cookie-manager";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";
import { ref } from "vue";
import ModelResultStatsLabel from "./ModelResultStatsLabel.vue";
import SearchInput from "@/Components/SearchInput.vue";
import Chip from "@/Components/Chip.vue";
import FileInput from "@/Components/FileInput.vue";

const props = defineProps({
    previousModel: {
        type: Object as () => ModelEntity | null,
        required: false,
        default: null,
    },
});

const emit = defineEmits(["modelTrained"]);

const datasetSources = ["Database", "Upload Files"];

const form = useForm({
    dataset_source: "Database",
    books_file: null,
    transactions_file: null,
    n_factors: null,
    n_epochs: null,
    lr_all: null,
    reg_all: null,
    cv: null,
    n_jobs: null,
});

const algorithms = [
    "Collaborative Filtering",
    "Content-Based",
    "Matrix Factorization",
];

const trainModelStatus = ref(null);
const model = ref<ModelEntity | null>(null);
const trainModelError = ref(null);

const startTraining = async () => {
    trainModelStatus.value = "loading";
    model.value = null;
    trainModelError.value = null;

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
                    trainModelError.value =
                        err.response.data.meta.message || "Training failed.";
                } else {
                    trainModelError.value = "Training failed.";
                }
                trainModelStatus.value = "error";
            });
    } catch (err) {
        trainModelError.value = "An unexpected error occurred.";
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
                <h3 class="text-lg font-semibold">Training Model</h3>
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
                <div class="flex flex-col w-full gap-4 sm:max-w-2xl">
                    <div
                        class="flex flex-col w-full gap-4 lg:flex-row lg:gap-6"
                    >
                        <!-- Dataset Source -->
                        <InputGroup id="dataset_source" label="Dataset Source">
                            <div class="flex gap-2">
                                <Chip
                                    v-for="source in datasetSources"
                                    :key="source"
                                    :label="source"
                                    :selected="form.dataset_source === source"
                                    class="whitespace-nowrap"
                                    @click="form.dataset_source = source"
                                />
                            </div>
                        </InputGroup>
                    </div>

                    <template v-if="form.dataset_source == 'Upload Files'">
                        <!-- Books File -->
                        <InputGroup id="books_file" label="Books File">
                            <FileInput
                                id="books_file"
                                :file="form.books_file"
                                :error="form.errors.books_file"
                                @update:file="
                                    (file) => (form.books_file = file)
                                "
                            />
                        </InputGroup>

                        <!-- Transactions File -->
                        <InputGroup
                            id="transactions_file"
                            label="Transactions File"
                        >
                            <FileInput
                                id="transactions_file"
                                :file="form.transactions_file"
                                :error="form.errors.transactions_file"
                                @update:file="
                                    (file) => (form.transactions_file = file)
                                "
                            />
                        </InputGroup>
                    </template>

                    <div class="flex gap-4">
                        <!-- Factors-->
                        <InputGroup id="n_factors" label="Factors">
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

                        <!-- Epochs -->
                        <InputGroup id="n_epochs" label="Epochs">
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
                                step="0.001"
                                placeholder="0.005"
                                :error="form.errors.lr_all"
                                @update:modelValue="form.errors.lr_all = null"
                            />
                            <template #suffix>
                                <InfoTooltip
                                    id="lr-all-info"
                                    text="The learning rate for optimization."
                                />
                            </template>
                        </InputGroup>

                        <!-- Regularization -->
                        <InputGroup id="reg_all" label="Regularization">
                            <TextInput
                                id="reg_all"
                                v-model="form.reg_all"
                                type="number"
                                step="0.001"
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
        <div
            v-if="model"
            class="mt-6 bg-green-100 text-green-800 p-4 rounded-lg"
        >
            <h2 class="font-semibold mb-2">Training Result</h2>
            <div
                class="rounded-lg py-4 px-6 bg-white flex flex-col xl:flex-row gap-4 items-center border border-green-400"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    class="size-9 mb-1 shrink-0 mt-2 xl:mt-0"
                >
                    <g clip-path="url(#clip0_478_4)">
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M17.1535 0.942111C17.6275 0.0756114 18.8725 0.0756114 19.3465 0.942111L20.3425 2.76311C20.549 3.14069 20.8594 3.45112 21.237 3.65761L23.058 4.65361C23.9245 5.12761 23.9245 6.37261 23.058 6.84661L21.237 7.84261C20.8594 8.0491 20.549 8.35954 20.3425 8.73711L19.3465 10.5581C18.8725 11.4246 17.6275 11.4246 17.1535 10.5581L16.1575 8.73711C15.951 8.35954 15.6406 8.0491 15.263 7.84261L13.442 6.84661C12.5755 6.37261 12.5755 5.12761 13.442 4.65361L15.263 3.65761C15.6406 3.45112 15.951 3.14069 16.1575 2.76311L17.1535 0.942111ZM14.1985 2.53011L12.7225 3.33761C10.815 4.38061 10.815 7.11961 12.7225 8.16261L14.5435 9.15861C14.6693 9.22742 14.7727 9.33084 14.8415 9.45661L15.8375 11.2776C16.4025 12.3111 17.4655 12.7846 18.4875 12.6976C17.9976 14.6994 16.8532 16.4802 15.236 17.7576C15.152 18.0567 15.0644 18.3547 14.973 18.6516C14.738 19.4186 14.0855 20.0061 13.247 20.0936C12.5205 20.1691 11.3195 20.2501 9.5 20.2501C7.6805 20.2501 6.4795 20.1691 5.753 20.0936C4.9145 20.0061 4.2625 19.4186 4.027 18.6516C3.93563 18.3547 3.84796 18.0567 3.764 17.7576C1.625 16.0646 0.25 13.4426 0.25 10.5001C0.25 5.39161 4.3915 1.25011 9.5 1.25011C11.215 1.25011 12.8215 1.71711 14.1985 2.53011ZM5.2235 21.2701C5.3535 21.3001 5.4865 21.3223 5.6225 21.3366C6.3985 21.4176 7.6435 21.5001 9.4995 21.5001C11.3555 21.5001 12.6 21.4176 13.3765 21.3366C13.5544 21.3181 13.7305 21.2852 13.903 21.2381L13.887 21.3881C13.7695 22.4706 12.9935 23.4516 11.8055 23.6081C11.0407 23.7048 10.2704 23.7522 9.4995 23.7501C8.5145 23.7501 7.75 23.6791 7.1885 23.5941C6.155 23.4376 5.4745 22.6166 5.302 21.6956C5.277 21.5626 5.25083 21.4204 5.2235 21.2701Z"
                            fill="currentColor"
                        />
                    </g>
                    <defs>
                        <clipPath id="clip0_478_4">
                            <rect width="24" height="24" fill="white" />
                        </clipPath>
                    </defs>
                </svg>

                <div
                    class="flex flex-col xl:flex-row gap-4 justify-between w-full items-center"
                >
                    <div class="w-full text-center xl:text-start">
                        <p
                            class="text-lg font-semibold text-ellipsis overflow-hidden whitespace-nowrap"
                        >
                            {{ model.filename }}
                        </p>
                        <p class="text-sm text-gray-600 mb-1">
                            {{ model.algorithm }} -
                            {{ $formatDate(model.created_at) }}
                        </p>
                    </div>

                    <!-- Stats -->
                    <div
                        class="flex flex-row max-xl:flex-wrap gap-4 items-center justify-center"
                    >
                        <ModelResultStatsLabel
                            label="Factors"
                            :value="model.n_factors.toString()"
                            :isHigher="
                                model.n_factors >
                                (props.previousModel
                                    ? props.previousModel.n_factors
                                    : 0)
                            "
                            :isLower="
                                model.n_factors <
                                (props.previousModel
                                    ? props.previousModel.n_factors
                                    : 0)
                            "
                        />
                        <ModelResultStatsLabel
                            label="Epochs"
                            :value="model.n_epochs.toString()"
                            :isHigher="
                                model.n_epochs >
                                (props.previousModel
                                    ? props.previousModel.n_epochs
                                    : 0)
                            "
                            :isLower="
                                model.n_epochs <
                                (props.previousModel
                                    ? props.previousModel.n_epochs
                                    : 0)
                            "
                        />
                        <ModelResultStatsLabel
                            label="RMSE"
                            :value="model.rmse.toFixed(4)"
                            :isHigher="
                                model.rmse >
                                (props.previousModel
                                    ? props.previousModel.rmse
                                    : Infinity)
                            "
                            :isLower="
                                model.rmse <
                                (props.previousModel
                                    ? props.previousModel.rmse
                                    : Infinity)
                            "
                        />
                        <ModelResultStatsLabel
                            label="MAE"
                            :value="model.mae.toFixed(4)"
                            :isHigher="
                                model.mae >
                                (props.previousModel
                                    ? props.previousModel.mae
                                    : Infinity)
                            "
                            :isLower="
                                model.mae <
                                (props.previousModel
                                    ? props.previousModel.mae
                                    : Infinity)
                            "
                        />
                    </div>
                </div>
            </div>
        </div>
        <div
            v-if="trainModelError"
            class="mt-6 bg-red-100 text-red-800 p-4 rounded-lg"
        >
            <h2 class="font-semibold mb-2">Error</h2>
            <pre class="whitespace-pre-wrap">{{ trainModelError }}</pre>
        </div>
    </div>
</template>
