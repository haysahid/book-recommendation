<script setup lang="ts">
import InfoTooltip from "@/Components/InfoTooltip.vue";
import InputGroup from "@/Components/InputGroup.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Chip from "@/Components/Chip.vue";
import FileInput from "@/Components/FileInput.vue";
import { useTrainingStore } from "@/stores/training-store";
import { computed, onMounted, ref } from "vue";
import TrainingResult from "./TrainingResult.vue";
import DialogModal from "@/Components/DialogModal.vue";
import CustomPageProps from "@/types/model/CustomPageProps";
import { usePage } from "@inertiajs/vue3";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import AutoTrainingModelForm from "./AutoTrainingModelForm.vue";
import { router } from "@inertiajs/vue3";

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
const showAutoTrainingModelDialog = ref(false);

const page = usePage<CustomPageProps>();

const isAutoTrainingEnabled = computed(() => {
    return page.props.setting.model.auto_training === true;
});

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
            <div class="flex gap-3 items-center">
                <SecondaryButton
                    type="button"
                    :disabled="trainingStore.trainModelStatus == 'loading'"
                    class="whitespace-nowrap px-2! sm:px-4!"
                    :class="{
                        'border-blue-500! hover:bg-blue-50! focus:ring-blue-500! text-blue-500!':
                            isAutoTrainingEnabled,
                    }"
                    @click="showAutoTrainingModelDialog = true"
                >
                    <template #prefix>
                        <svg
                            v-if="isAutoTrainingEnabled"
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            class="size-5 text-blue-500"
                        >
                            <path
                                d="M19.9998 7L20.9398 4.94L22.9998 4L20.9398 3.06L19.9998 1L19.0598 3.06L16.9998 4L19.0598 4.94L19.9998 7ZM8.49984 7L9.43984 4.94L11.4998 4L9.43984 3.06L8.49984 1L7.55984 3.06L5.49984 4L7.55984 4.94L8.49984 7ZM19.9998 12.5L19.0598 14.56L16.9998 15.5L19.0598 16.44L19.9998 18.5L20.9398 16.44L22.9998 15.5L20.9398 14.56L19.9998 12.5ZM18.4098 9.83L14.1698 5.59L1.58984 18.17L5.82984 22.41L18.4098 9.83ZM14.2098 11.21L12.7998 9.8L14.1798 8.42L15.5898 9.83L14.2098 11.21Z"
                                fill="currentColor"
                            />
                        </svg>
                        <svg
                            v-else
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            class="size-5 text-gray-600"
                        >
                            <path
                                d="M20.0001 7L20.9401 4.94L23.0001 4L20.9401 3.06L20.0001 1L19.0601 3.06L17.0001 4L19.0601 4.94L20.0001 7ZM14.1701 8.42L15.5801 9.83L14.1201 11.29L15.5401 12.71L18.4101 9.83L14.1701 5.59L11.2901 8.46L12.7101 9.88L14.1701 8.42ZM1.39014 4.22L8.46014 11.29L1.59014 18.17L5.83014 22.41L12.7101 15.54L19.7801 22.61L21.1901 21.19L2.81014 2.81L1.39014 4.22Z"
                                fill="currentColor"
                            />
                        </svg>
                    </template>

                    <span class="hidden sm:inline-block">Auto Training</span>
                </SecondaryButton>
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
        </div>

        <form
            @submit.prevent="
                trainingStore.startTraining({
                    onSuccess: () => emit('modelTrained'),
                })
            "
        >
            <div class="flex flex-col w-full gap-4 lg:flex-row lg:gap-6">
                <div class="flex flex-col w-full gap-4 sm:max-w-3xl">
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

        <!-- From Tuning Dialog -->
        <DialogModal
            :show="showAutoTrainingModelDialog"
            @close="showAutoTrainingModelDialog = false"
            max-width="md"
        >
            <template #content>
                <AutoTrainingModelForm
                    @enabled="
                        showAutoTrainingModelDialog = false;
                        router.reload();
                    "
                    @updated="
                        showAutoTrainingModelDialog = false;
                        router.reload();
                    "
                    @disabled="
                        showAutoTrainingModelDialog = false;
                        router.reload();
                    "
                />
            </template>
        </DialogModal>
    </div>
</template>
