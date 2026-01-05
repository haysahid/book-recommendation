<script setup lang="ts">
import InfoTooltip from "@/Components/InfoTooltip.vue";
import InputGroup from "@/Components/InputGroup.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Chip from "@/Components/Chip.vue";
import FileInput from "@/Components/FileInput.vue";
import QuantityInput from "@/Components/QuantityInput.vue";
import { useTuningStore } from "@/stores/tuning-store";
import TuningDetail from "./TuningDetail.vue";
import InputError from "@/Components/InputError.vue";
import { nextTick, onMounted } from "vue";
import { useTrainingStore } from "@/stores/training-store";
import ModelResultStats from "./ModelResultStats.vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({});

const tuningStore = useTuningStore();
const trainingStore = useTrainingStore();

const applyToTrainModel = () => {
    if (tuningStore.result) {
        const bestParams = tuningStore.result.best_params;

        trainingStore.form.dataset_source = "Database";
        trainingStore.form.reference = tuningStore.form.reference;
        trainingStore.form.n_factors = bestParams.n_factors;
        trainingStore.form.n_epochs = bestParams.n_epochs;
        trainingStore.form.lr_all = bestParams.lr_all;
        trainingStore.form.reg_all = bestParams.reg_all;

        trainingStore.clearTrainingResult();
    }

    router.visit("/admin/recommendation-system/training-model?from_tuning=1");
};

const startTuning = () => {
    tuningStore.startTuning({
        onSuccess: () => {
            nextTick(() => {
                setTimeout(() => {
                    const tuningResult = document.getElementById(
                        "tuningResultSection"
                    );
                    const mainArea = document.getElementById("main-area");
                    if (tuningResult && mainArea) {
                        mainArea.scrollTo({
                            top: tuningResult.getBoundingClientRect().top,
                            behavior: "smooth",
                        });
                    }
                }, 300);
            });
        },
    });
};

onMounted(() => {
    tuningStore.clearErrors();
});
</script>

<template>
    <div>
        <div
            id="tuningHeaderSection"
            class="flex gap-2.5 items-center justify-between mb-4"
        >
            <div>
                <h3 class="text-lg font-semibold">Tuning Model</h3>
                <p class="text-sm text-gray-500">
                    Adjust hyperparameters to optimize model performance.
                </p>
            </div>
            <PrimaryButton
                type="submit"
                :disabled="tuningStore.tuneModelStatus == 'loading'"
                class="whitespace-nowrap"
                @click="startTuning()"
            >
                {{
                    tuningStore.tuneModelStatus == "loading"
                        ? "Tuning..."
                        : "Start Tuning"
                }}
            </PrimaryButton>
        </div>

        <div
            id="tuningFormSection"
            class="flex flex-col w-full gap-4 lg:flex-row lg:gap-6"
        >
            <div class="flex flex-col w-full gap-4">
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 sm:max-w-3xl">
                    <!-- Dataset Source -->
                    <InputGroup
                        id="tuning_dataset_source"
                        label="Dataset Source"
                        class="col-span-2 md:col-span-1 xl:col-span-1"
                    >
                        <div class="flex gap-2">
                            <Chip
                                v-for="source in tuningStore.datasetSources"
                                :key="source"
                                :label="source"
                                :selected="
                                    tuningStore.form.dataset_source === source
                                "
                                class="whitespace-nowrap"
                                @click="
                                    tuningStore.form.dataset_source = source
                                "
                            />
                        </div>
                    </InputGroup>

                    <!-- Reference -->
                    <InputGroup
                        id="reference"
                        label="Reference"
                        class="col-span-2 md:col-span-1 lg:col-span-2"
                    >
                        <div class="flex gap-2">
                            <Chip
                                v-for="(label, value) in tuningStore.references"
                                :key="value"
                                :label="label"
                                :selected="tuningStore.form.reference === value"
                                class="whitespace-nowrap"
                                @click="tuningStore.form.reference = value"
                            />
                        </div>

                        <template #suffix>
                            <InfoTooltip
                                id="reference-info"
                                text="The type of recommendation reference used in tuning."
                            />
                        </template>
                    </InputGroup>

                    <div
                        v-show="
                            tuningStore.form.dataset_source == 'Upload Files'
                        "
                        class="col-span-2 lg:col-span-3"
                    >
                        <!-- Books File -->
                        <InputGroup id="tuning_books_file" label="Books File">
                            <FileInput
                                id="tuning_books_file"
                                :file="tuningStore.form.books_file"
                                :error="tuningStore.form.errors?.books_file"
                                @update:file="
                                    (file) =>
                                        (tuningStore.form.books_file = file)
                                "
                                :containerClass="
                                    tuningStore.form.books_file
                                        ? 'border-gray-400'
                                        : ''
                                "
                            />
                        </InputGroup>

                        <!-- Transactions File -->
                        <InputGroup
                            id="tuning_transactions_file"
                            label="Transactions File"
                        >
                            <FileInput
                                id="tuning_transactions_file"
                                :file="tuningStore.form.transactions_file"
                                :error="
                                    tuningStore.form.errors?.transactions_file
                                "
                                @update:file="
                                    (file) =>
                                        (tuningStore.form.transactions_file =
                                            file)
                                "
                                :containerClass="
                                    tuningStore.form.transactions_file
                                        ? 'border-gray-400'
                                        : ''
                                "
                            />
                        </InputGroup>
                    </div>

                    <!-- Cross-Validation Folds -->
                    <InputGroup
                        id="tuning_cv"
                        label="Cross-Validation Folds"
                        class="whitespace-nowrap col-span-2 sm:col-span-1"
                    >
                        <QuantityInput
                            id="tuning_cv"
                            v-model.number="tuningStore.form.cv"
                            placeholder="5"
                            :min="2"
                            :max="10"
                            :step="1"
                            :showAvailability="false"
                            :label="null"
                            :error="tuningStore.form.errors?.cv"
                            @update:modelValue="
                                tuningStore.form.errors.cv = null
                            "
                        />
                        <template #suffix>
                            <InfoTooltip
                                id="cv-info"
                                text="Number of folds for cross-validation."
                            />
                        </template>
                    </InputGroup>

                    <!-- Jobs -->
                    <InputGroup id="tuning_n_jobs" label="Jobs">
                        <QuantityInput
                            id="tuning_n_jobs"
                            v-model.number="tuningStore.form.n_jobs"
                            placeholder="1"
                            :min="-1"
                            :max="10"
                            :step="1"
                            :showAvailability="false"
                            :label="null"
                            :error="tuningStore.form.errors?.n_jobs"
                            @update:modelValue="
                                tuningStore.form.errors.n_jobs = null
                            "
                            :bgClass="
                                tuningStore.form.n_jobs ? 'border-gray-400' : ''
                            "
                        />
                        <template #suffix>
                            <InfoTooltip
                                id="tuning-n-jobs-info"
                                text="Number of parallel jobs to run."
                            />
                        </template>
                    </InputGroup>

                    <!-- Grid Size -->
                    <InputGroup id="tuning_grid_size" label="Grid Size">
                        <QuantityInput
                            id="tuning_grid_size"
                            v-model="tuningStore.gridSize"
                            :min="2"
                            :max="10"
                            :step="1"
                            :label="null"
                            :show-availability="false"
                        />
                        <template #suffix>
                            <InfoTooltip
                                id="tuning-grid-size-info"
                                text="Number of values to try for each hyperparameter."
                            />
                        </template>
                    </InputGroup>
                </div>

                <h3 class="text-md font-semibold mt-2">Hyperparameter Grid</h3>

                <div class="flex gap-4 flex-col w-full">
                    <!-- Factors-->
                    <InputGroup id="tuning_n_factors" label="Factors">
                        <div
                            class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:flex gap-2"
                        >
                            <TextInput
                                v-for="(value, index) in tuningStore.gridSize"
                                :key="index"
                                :id="`tuning_n_factors_${index}`"
                                :modelValue="
                                    tuningStore.form.n_factors
                                        ? tuningStore.form.n_factors[index] ??
                                          null
                                        : null
                                "
                                type="number"
                                :placeholder="
                                    tuningStore.nFactorsPlaceholder[
                                        index
                                    ]?.toString() ?? null
                                "
                                :bgClass="
                                    tuningStore.form.n_factors[index]
                                        ? 'border-gray-400'
                                        : ''
                                "
                                :error="tuningStore.form.errors?.n_factors"
                                @update:modelValue="
                                    (value) => {
                                        tuningStore.form.n_factors[index] =
                                            value;
                                        tuningStore.form.errors.n_factors =
                                            null;
                                    }
                                "
                                class="w-full"
                            />
                        </div>

                        <InputError
                            :message="tuningStore.form.errors?.n_factors"
                            class="px-2 mt-1"
                        />

                        <template #suffix>
                            <InfoTooltip
                                id="tuning-n-factors-info"
                                text="Number of latent factors used in the model."
                            />
                        </template>
                    </InputGroup>

                    <!-- Epochs -->
                    <InputGroup id="tuning_n_epochs" label="Epochs">
                        <div
                            class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:flex gap-2"
                        >
                            <TextInput
                                v-for="(value, index) in tuningStore.gridSize"
                                :key="index"
                                :id="`tuning_n_epochs_${index}`"
                                :modelValue="
                                    tuningStore.form.n_epochs
                                        ? tuningStore.form.n_epochs[index] ??
                                          null
                                        : null
                                "
                                type="number"
                                :placeholder="
                                    tuningStore.nEpochsPlaceholder[
                                        index
                                    ]?.toString() ?? null
                                "
                                :bgClass="
                                    tuningStore.form.n_epochs[index]
                                        ? 'border-gray-400'
                                        : ''
                                "
                                @update:modelValue="
                                    (value) => {
                                        tuningStore.form.n_epochs[index] =
                                            value;
                                        tuningStore.form.errors.n_epochs = null;
                                    }
                                "
                                class="w-full"
                            />
                        </div>

                        <InputError
                            :message="tuningStore.form.errors?.n_epochs"
                            class="px-2 mt-1"
                        />

                        <template #suffix>
                            <InfoTooltip
                                id="tuning-n-epochs-info"
                                text="Number of training iterations."
                            />
                        </template>
                    </InputGroup>

                    <!-- Learning Rate -->
                    <InputGroup id="tuning_lr_all" label="Learning Rate">
                        <div
                            class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:flex gap-2"
                        >
                            <TextInput
                                v-for="(value, index) in tuningStore.gridSize"
                                :key="index"
                                :id="`tuning_lr_all_${index}`"
                                :modelValue="
                                    tuningStore.form.lr_all
                                        ? tuningStore.form.lr_all[index] ?? null
                                        : null
                                "
                                type="number"
                                :placeholder="
                                    tuningStore.lrAllPlaceholder[
                                        index
                                    ]?.toString() ?? null
                                "
                                :bgClass="
                                    tuningStore.form.lr_all[index]
                                        ? 'border-gray-400'
                                        : ''
                                "
                                :error="tuningStore.form.errors?.lr_all"
                                @update:modelValue="
                                    (value) => {
                                        tuningStore.form.lr_all[index] = value;
                                        tuningStore.form.errors.lr_all = null;
                                    }
                                "
                                class="w-full"
                            />
                        </div>

                        <InputError
                            :message="tuningStore.form.errors?.lr_all"
                            class="px-2 mt-1"
                        />

                        <template #suffix>
                            <InfoTooltip
                                id="tuning-lr-all-info"
                                text="Learning rate for the model."
                            />
                        </template>
                    </InputGroup>

                    <!-- Regularization -->
                    <InputGroup id="tuning_reg_all" label="Regularization">
                        <div
                            class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:flex gap-2"
                        >
                            <TextInput
                                v-for="(value, index) in tuningStore.gridSize"
                                :key="index"
                                :id="`tuning_reg_all_${index}`"
                                :modelValue="
                                    tuningStore.form.reg_all
                                        ? tuningStore.form.reg_all[index] ??
                                          null
                                        : null
                                "
                                type="number"
                                :placeholder="
                                    tuningStore.regAllPlaceholder[
                                        index
                                    ]?.toString() ?? null
                                "
                                :bgClass="
                                    tuningStore.form.reg_all[index]
                                        ? 'border-gray-400'
                                        : ''
                                "
                                :error="tuningStore.form.errors?.reg_all"
                                @update:modelValue="
                                    (value) => {
                                        tuningStore.form.reg_all[index] = value;
                                        tuningStore.form.errors.reg_all = null;
                                    }
                                "
                                class="w-full"
                            />
                        </div>

                        <InputError
                            :message="tuningStore.form.errors?.reg_all"
                            class="px-2 mt-1"
                        />

                        <template #suffix>
                            <InfoTooltip
                                id="tuning-reg-all-info"
                                text="Regularization parameter for the model."
                            />
                        </template>
                    </InputGroup>
                </div>
            </div>
        </div>

        <!-- Error -->
        <Transition name="accordion">
            <div
                v-if="tuningStore.tuneModelError"
                class="mt-6 bg-red-100 text-red-800 p-4 rounded-lg"
            >
                <div class="flex items-center justify-between gap-4 mb-2">
                    <h2 class="font-semibold">Error</h2>
                    <button
                        class="whitespace-nowrap text-sm text-red-700 hover:underline hover:text-red-600"
                        @click="tuningStore.clearTuningResult"
                    >
                        Clear Error
                    </button>
                </div>
                <pre class="whitespace-pre-wrap">{{
                    tuningStore.tuneModelError
                }}</pre>
            </div>
        </Transition>

        <!-- Success -->
        <Transition name="accordion">
            <div
                v-if="tuningStore.result"
                id="tuningResultSection"
                class="mt-6 bg-green-100 p-4 rounded-lg flex flex-col gap-3"
            >
                <div
                    class="flex items-center justify-between gap-4 text-green-800"
                >
                    <h2 class="font-semibold text-lg">Tuning Result</h2>
                    <div class="flex gap-4">
                        <button
                            class="whitespace-nowrap text-sm text-red-700 hover:underline hover:text-red-600"
                            @click="tuningStore.clearTuningResult"
                        >
                            Clear Result
                        </button>
                        <PrimaryButton @click="applyToTrainModel()">
                            Apply to Train Model
                        </PrimaryButton>
                    </div>
                </div>

                <div
                    class="rounded-lg py-4 px-6 bg-white border border-green-400 text-green-800 flex flex-col gap-3.5"
                >
                    <ModelResultStats
                        :model="({
                        id: null,
                        reference: tuningStore.form.reference,
                        n_factors: tuningStore.result.best_params.n_factors,
                        n_epochs: tuningStore.result.best_params.n_epochs,
                        lr_all: tuningStore.result.best_params.lr_all,
                        reg_all: tuningStore.result.best_params.reg_all,
                        rmse: tuningStore.result.best_score_rmse,
                        mae: tuningStore.result.best_score_mae,
                        created_at: new Date().toISOString(),
                    } as ModelEntity)"
                        :compareModel="trainingStore.activeModel"
                        :showSubTitles="true"
                    />

                    <div>
                        <p class="text-sm text-gray-500 mb-2">
                            Compared to the active model:
                        </p>

                        <ModelResultStats
                            :model="trainingStore.activeModel"
                            class="text-primary"
                        />
                    </div>
                </div>

                <div
                    class="rounded-lg py-4 px-6 bg-white flex flex-row gap-4 items-start border border-green-400"
                >
                    <TuningDetail
                        v-if="tuningStore.result"
                        :result="tuningStore.result.cv_results"
                    />
                </div>
            </div>
        </Transition>
    </div>
</template>
