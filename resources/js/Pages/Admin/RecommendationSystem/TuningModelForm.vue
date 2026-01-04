<script setup lang="ts">
import InfoTooltip from "@/Components/InfoTooltip.vue";
import InputGroup from "@/Components/InputGroup.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import ModelResultStatsLabel from "./ModelResultStatsLabel.vue";
import Chip from "@/Components/Chip.vue";
import FileInput from "@/Components/FileInput.vue";
import QuantityInput from "@/Components/QuantityInput.vue";
import { useTuningStore } from "@/stores/tuning-store";
import TuningDetail from "./TuningDetail.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({});

const emit = defineEmits(["tuned"]);

const tuningStore = useTuningStore();
</script>

<template>
    <div>
        <div class="flex gap-2.5 items-center justify-between mb-4">
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
                @click="tuningStore.startTuning"
            >
                {{
                    tuningStore.tuneModelStatus == "loading"
                        ? "Tuning..."
                        : "Start Tuning"
                }}
            </PrimaryButton>
        </div>

        <div class="flex flex-col w-full gap-4 lg:flex-row lg:gap-6">
            <div class="flex flex-col w-full gap-4">
                <div class="flex flex-col gap-4">
                    <!-- Dataset Source -->
                    <InputGroup
                        id="tuning_dataset_source"
                        label="Dataset Source"
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

                    <template
                        v-if="tuningStore.form.dataset_source == 'Upload Files'"
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
                            />
                        </InputGroup>
                    </template>

                    <div class="flex flex-col sm:flex-row gap-4 sm:max-w-2xl">
                        <!-- Grid Size -->
                        <InputGroup
                            id="tuning_grid_size"
                            label="Grid Size"
                            class="w-min"
                        >
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

                        <div class="flex gap-4 w-full">
                            <!-- Cross-Validation Folds -->
                            <InputGroup
                                id="tuning_cv"
                                label="Cross-Validation Folds"
                            >
                                <TextInput
                                    id="tuning_cv"
                                    v-model="tuningStore.form.cv"
                                    type="number"
                                    placeholder="3"
                                    :error="tuningStore.form.errors?.cv"
                                    @update:modelValue="
                                        tuningStore.form.errors.cv = null
                                    "
                                    class="w-full"
                                    :bgClass="
                                        tuningStore.form.cv
                                            ? 'border-gray-400'
                                            : ''
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
                                <TextInput
                                    id="tuning_n_jobs"
                                    v-model="tuningStore.form.n_jobs"
                                    type="number"
                                    placeholder="1"
                                    :error="tuningStore.form.errors?.n_jobs"
                                    @update:modelValue="
                                        tuningStore.form.errors.n_jobs = null
                                    "
                                    :bgClass="
                                        tuningStore.form.n_jobs
                                            ? 'border-gray-400'
                                            : ''
                                    "
                                />
                                <template #suffix>
                                    <InfoTooltip
                                        id="tuning-n-jobs-info"
                                        text="Number of parallel jobs to run."
                                    />
                                </template>
                            </InputGroup>
                        </div>
                    </div>
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
                class="mt-6 bg-green-100 p-4 rounded-lg"
            >
                <div
                    class="flex items-center justify-between gap-4 mb-2 text-green-800"
                >
                    <h2 class="font-semibold">Tuning Result</h2>
                    <div class="flex gap-4">
                        <button
                            class="whitespace-nowrap text-sm text-red-700 hover:underline hover:text-red-600"
                            @click="tuningStore.clearTuningResult"
                        >
                            Clear Result
                        </button>
                    </div>
                </div>

                <div
                    class="rounded-lg py-4 px-6 bg-white flex flex-row gap-4 items-start border border-green-400 mb-2 text-green-800"
                >
                    <div class="w-full lg:w-2/3">
                        <h3 class="text-md font-semibold mb-2">
                            Best Hyperparameters
                        </h3>
                        <div
                            class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-2 p-2 justify-start bg-gray-50 w-full rounded-md border border-gray-200"
                        >
                            <ModelResultStatsLabel
                                label="Factors"
                                :value="
                                    tuningStore.result.best_params?.n_factors?.toString() ??
                                    '-'
                                "
                                :isHigher="false"
                                :isLower="false"
                                class="items-start!"
                            />
                            <ModelResultStatsLabel
                                label="Epochs"
                                :value="
                                    tuningStore.result.best_params?.n_epochs?.toString() ??
                                    '-'
                                "
                                :isHigher="false"
                                :isLower="false"
                                class="items-start!"
                            />
                            <ModelResultStatsLabel
                                label="Learning Rate"
                                :value="
                                    tuningStore.result.best_params?.lr_all?.toString() ??
                                    '-'
                                "
                                :isHigher="false"
                                :isLower="false"
                                class="items-start!"
                            />
                            <ModelResultStatsLabel
                                label="Regularization"
                                :value="
                                    tuningStore.result.best_params?.reg_all?.toString() ??
                                    '-'
                                "
                                :isHigher="false"
                                :isLower="false"
                                class="items-start!"
                            />
                        </div>
                    </div>

                    <div class="w-full lg:w-1/3">
                        <h3 class="text-md font-semibold mb-2">Best Scores</h3>
                        <div
                            class="grid grid-cols-1 xl:grid-cols-2 gap-2 p-2 justify-start bg-gray-50 w-full rounded-md border border-gray-200"
                        >
                            <ModelResultStatsLabel
                                label="RMSE Score"
                                :value="
                                    tuningStore.result.best_score_rmse.toFixed(
                                        4
                                    )
                                "
                                :isHigher="false"
                                :isLower="false"
                                class="items-start!"
                            />
                            <ModelResultStatsLabel
                                label="MAE Score"
                                :value="
                                    tuningStore.result.best_score_mae.toFixed(4)
                                "
                                :isHigher="false"
                                :isLower="false"
                                class="items-start!"
                            />
                        </div>
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
