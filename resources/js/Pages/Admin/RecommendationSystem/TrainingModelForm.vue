<script setup lang="ts">
import InfoTooltip from "@/Components/InfoTooltip.vue";
import InputGroup from "@/Components/InputGroup.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Chip from "@/Components/Chip.vue";
import FileInput from "@/Components/FileInput.vue";
import { useTrainingStore } from "@/stores/training-store";
import { onMounted, ref } from "vue";
import TrainingResult from "./TrainingResult.vue";
import { useDialogStore } from "@/stores/dialog-store";
import DialogModal from "@/Components/DialogModal.vue";

const props = defineProps({
    previousModel: {
        type: Object as () => ModelEntity | null,
        required: false,
        default: null,
    },
});

const emit = defineEmits(["modelTrained"]);

const trainingStore = useTrainingStore();

const showFromTuningDialog = ref(false);

onMounted(() => {
    trainingStore.clearErrors();

    const urlParams = new URLSearchParams(window.location.search);
    const isFromTuning = urlParams.get("from_tuning") === "1";

    if (isFromTuning) {
        showFromTuningDialog.value = true;

        // Remove the query parameter from the URL
        const url = new URL(window.location.href);
        url.searchParams.delete("from_tuning");
        window.history.replaceState({}, document.title, url.toString());
    }
});
</script>

<template>
    <div class="flex flex-col gap-4 transition-all duration-300 ease-in-out">
        <div class="flex gap-2.5 items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold">Training Model</h3>
                <p class="text-sm text-gray-500">
                    Train the recommendation system model with new data.
                </p>
            </div>
            <PrimaryButton
                type="submit"
                :disabled="trainingStore.trainModelStatus == 'loading'"
                class="whitespace-nowrap"
                @click="
                    trainingStore.startTraining({
                        onSuccess: () => emit('modelTrained'),
                    })
                "
            >
                {{
                    trainingStore.trainModelStatus == "loading"
                        ? "Training..."
                        : "Start Training"
                }}
            </PrimaryButton>
        </div>

        <form
            @submit.prevent="
                trainingStore.startTraining({
                    onSuccess: () => emit('modelTrained'),
                })
            "
        >
            <div class="flex flex-col w-full gap-4 lg:flex-row lg:gap-6">
                <div class="flex flex-col w-full gap-4 sm:max-w-2xl">
                    <div class="flex flex-col w-full gap-4 sm:flex-row">
                        <!-- Dataset Source -->
                        <InputGroup id="dataset_source" label="Dataset Source">
                            <div class="flex gap-2">
                                <Chip
                                    v-for="source in trainingStore.datasetSources"
                                    :key="source"
                                    :label="source"
                                    :selected="
                                        trainingStore.form.dataset_source ===
                                        source
                                    "
                                    class="whitespace-nowrap"
                                    @click="
                                        trainingStore.form.dataset_source =
                                            source
                                    "
                                />
                            </div>
                        </InputGroup>

                        <!-- Reference -->
                        <InputGroup id="reference" label="Reference">
                            <div class="flex gap-2">
                                <Chip
                                    v-for="(
                                        label, value
                                    ) in trainingStore.references"
                                    :key="value"
                                    :label="label"
                                    :selected="
                                        trainingStore.form.reference === value
                                    "
                                    class="whitespace-nowrap"
                                    @click="
                                        trainingStore.form.reference = value
                                    "
                                />
                            </div>

                            <template #suffix>
                                <InfoTooltip
                                    id="reference-info"
                                    text="The type of recommendation reference used in training."
                                />
                            </template>
                        </InputGroup>
                    </div>

                    <div
                        v-show="
                            trainingStore.form.dataset_source == 'Upload Files'
                        "
                        class="flex flex-col w-full gap-4"
                    >
                        <!-- Books File -->
                        <InputGroup id="books_file" label="Books File">
                            <FileInput
                                id="books_file"
                                :file="trainingStore.form.books_file"
                                :error="trainingStore.form.errors.books_file"
                                @update:file="
                                    (file) =>
                                        (trainingStore.form.books_file = file)
                                "
                                :containerClass="
                                    trainingStore.form.books_file
                                        ? 'border-gray-400'
                                        : ''
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
                                :file="trainingStore.form.transactions_file"
                                :error="
                                    trainingStore.form.errors.transactions_file
                                "
                                @update:file="
                                    (file) =>
                                        (trainingStore.form.transactions_file =
                                            file)
                                "
                                :containerClass="
                                    trainingStore.form.transactions_file
                                        ? 'border-gray-400'
                                        : ''
                                "
                            />
                        </InputGroup>
                    </div>

                    <div class="flex gap-4">
                        <!-- Factors-->
                        <InputGroup id="n_factors" label="Factors">
                            <TextInput
                                id="n_factors"
                                v-model="trainingStore.form.n_factors"
                                type="number"
                                placeholder="100"
                                :error="trainingStore.form.errors.n_factors"
                                @update:modelValue="
                                    trainingStore.form.errors.n_factors = null
                                "
                                :bgClass="
                                    trainingStore.form.n_factors
                                        ? 'border-gray-400'
                                        : ''
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
                                v-model="trainingStore.form.n_epochs"
                                type="number"
                                placeholder="20"
                                :error="trainingStore.form.errors.n_epochs"
                                @update:modelValue="
                                    trainingStore.form.errors.n_epochs = null
                                "
                                :bgClass="
                                    trainingStore.form.n_epochs
                                        ? 'border-gray-400'
                                        : ''
                                "
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
                                v-model="trainingStore.form.lr_all"
                                type="number"
                                step="0.001"
                                placeholder="0.005"
                                :error="trainingStore.form.errors.lr_all"
                                @update:modelValue="
                                    trainingStore.form.errors.lr_all = null
                                "
                                :bgClass="
                                    trainingStore.form.lr_all
                                        ? 'border-gray-400'
                                        : ''
                                "
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
                                v-model="trainingStore.form.reg_all"
                                type="number"
                                step="0.001"
                                placeholder="0.02"
                                :error="trainingStore.form.errors.reg_all"
                                @update:modelValue="
                                    trainingStore.form.errors.reg_all = null
                                "
                                :bgClass="
                                    trainingStore.form.reg_all
                                        ? 'border-gray-400'
                                        : ''
                                "
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

        <!-- Error -->
        <Transition name="fade">
            <div
                v-if="trainingStore.trainModelError"
                class="bg-red-100 text-red-800 p-4 rounded-lg"
            >
                <div class="flex items-center justify-between gap-4 mb-2">
                    <h2 class="font-semibold">Error</h2>
                    <button
                        class="whitespace-nowrap text-sm text-red-700 hover:underline hover:text-red-600"
                        @click="trainingStore.clearTrainingResult"
                    >
                        Clear Error
                    </button>
                </div>
                <pre class="whitespace-pre-wrap">{{
                    trainingStore.trainModelError
                }}</pre>
            </div>
        </Transition>

        <!-- Success -->
        <Transition name="fade">
            <div
                v-if="trainingStore.trainedModel"
                class="bg-green-100 text-green-800 p-4 rounded-lg"
            >
                <div
                    class="flex items-center justify-between gap-4 mb-2 text-green-800"
                >
                    <h2 class="font-semibold">Training Result</h2>
                    <div class="flex gap-4">
                        <button
                            class="whitespace-nowrap text-sm text-red-700 hover:underline hover:text-red-600"
                            @click="trainingStore.clearTrainingResult"
                        >
                            Clear Result
                        </button>
                    </div>
                </div>

                <TrainingResult
                    :model="trainingStore.trainedModel"
                    :previousModel="previousModel"
                />
            </div>
        </Transition>

        <!-- From Tuning Dialog -->
        <DialogModal
            :show="showFromTuningDialog"
            @close="showFromTuningDialog = false"
            max-width="md"
        >
            <template #icon>
                <div class="p-2">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="64"
                        height="64"
                        viewBox="0 0 64 64"
                        fill="none"
                    >
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M31.7543 63.4159C35.9122 63.4159 40.0293 62.5969 43.8707 61.0058C47.712 59.4146 51.2024 57.0824 54.1424 54.1424C57.0824 51.2024 59.4146 47.712 61.0058 43.8707C62.5969 40.0293 63.4159 35.9122 63.4159 31.7543C63.4159 27.5965 62.5969 23.4793 61.0058 19.638C59.4146 15.7966 57.0824 12.3063 54.1424 9.36622C51.2024 6.42618 47.712 4.09401 43.8707 2.50286C40.0293 0.911723 35.9122 0.0927734 31.7543 0.0927734C23.3572 0.0927736 15.3039 3.42853 9.36622 9.36622C3.42853 15.3039 0.0927734 23.3572 0.0927734 31.7543C0.0927734 40.1515 3.42853 48.2047 9.36622 54.1424C15.3039 60.0801 23.3572 63.4159 31.7543 63.4159ZM30.9381 44.5596L48.5279 23.452L43.1243 18.949L27.9971 37.0981L20.1697 29.2671L15.1953 34.2415L25.7492 44.7953L28.4721 47.5182L30.9381 44.5596Z"
                            fill="#27D04D"
                        />
                    </svg>
                </div>
            </template>
            <template #title> Best Tuning Parameters Applied! </template>
            <template #content>
                <!-- Paragraph -->
                <p class="max-w-prose">
                    The best parameters from the tuning process have been
                    applied to the training form.
                </p>
            </template>
            <template #footer>
                <PrimaryButton
                    class="mt-2"
                    @click="
                        showFromTuningDialog = false;
                        trainingStore.startTraining({
                            onSuccess: () => emit('modelTrained'),
                        });
                    "
                >
                    Start Training
                </PrimaryButton>
            </template>
        </DialogModal>
    </div>
</template>
