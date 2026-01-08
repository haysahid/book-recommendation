<script setup lang="ts">
import InfoTooltip from "@/Components/InfoTooltip.vue";
import InputGroup from "@/Components/InputGroup.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import CustomPageProps from "@/types/model/CustomPageProps";
import { useTrainingStore } from "@/stores/training-store";
import { useDialogStore } from "@/stores/dialog-store";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import Chip from "@/Components/Chip.vue";

const page = usePage<CustomPageProps>();

const emit = defineEmits(["enabled", "updated", "disabled"]);

const currentConfig = page.props.setting.model || null;
const isActive = page.props.setting.model?.auto_training;

const trainingStore = useTrainingStore();

const intervalUnits = [
    { value: "seconds", label: "Seconds" },
    { value: "minutes", label: "Minutes" },
    { value: "hours", label: "Hours" },
    { value: "days", label: "Days" },
];

const form = useForm({
    reference: currentConfig?.reference || "rating",
    n_factors: currentConfig?.n_factors || null,
    n_epochs: currentConfig?.n_epochs || null,
    lr_all: currentConfig?.lr_all || null,
    reg_all: currentConfig?.reg_all || null,
    auto_training_interval: currentConfig.auto_training_interval,
    interval_unit: currentConfig.interval_unit || "seconds",
});

const validate = () => {
    form.clearErrors();
    if (form.n_factors === null || form.n_factors === "") {
        form.setError("n_factors", "Factors is required.");
    } else if (isNaN(form.n_factors) || parseInt(form.n_factors) <= 0) {
        form.setError("n_factors", "Factors must be a positive integer.");
    }

    if (form.n_epochs === null || form.n_epochs === "") {
        form.setError("n_epochs", "Epochs is required.");
    } else if (isNaN(form.n_epochs) || parseInt(form.n_epochs) <= 0) {
        form.setError("n_epochs", "Epochs must be a positive integer.");
    }

    if (form.lr_all === null || form.lr_all === "") {
        form.setError("lr_all", "Learning Rate is required.");
    } else if (isNaN(form.lr_all) || parseFloat(form.lr_all) <= 0) {
        form.setError("lr_all", "Learning Rate must be a positive number.");
    }

    if (form.reg_all === null || form.reg_all === "") {
        form.setError("reg_all", "Regularization is required.");
    } else if (isNaN(form.reg_all) || parseFloat(form.reg_all) <= 0) {
        form.setError("reg_all", "Regularization must be a positive number.");
    }

    if (form.auto_training_interval === null) {
        form.setError(
            "auto_training_interval",
            "Auto Training Interval is required."
        );
    } else if (
        isNaN(form.auto_training_interval) ||
        form.auto_training_interval <= 0
    ) {
        form.setError(
            "auto_training_interval",
            "Auto Training Interval must be a positive integer."
        );
    }

    if (form.interval_unit === null || form.interval_unit === "") {
        form.setError("interval_unit", "Interval Unit is required.");
    }

    return Object.keys(form.errors).length === 0;
};

const enableAutoTrainingModel = () => {
    if (!validate()) return;

    trainingStore.setAutoTraining(
        {
            autoTraining: true,
            autoTrainingInterval: parseFloat(form.auto_training_interval),
            intervalUnit: form.interval_unit,
            reference: form.reference,
            nFactors: form.n_factors ? parseInt(form.n_factors) : null,
            nEpochs: form.n_epochs ? parseInt(form.n_epochs) : null,
            lrAll: form.lr_all ? parseFloat(form.lr_all) : null,
            regAll: form.reg_all ? parseFloat(form.reg_all) : null,
        },
        {
            onSuccess: () => {
                useDialogStore().openSuccessDialog(
                    "Auto-training model has been enabled."
                );
                emit("enabled");
            },
        }
    );
};

const disableAutoTrainingModel = () => {
    trainingStore.setAutoTraining(
        {
            autoTraining: false,
            reference: form.reference,
        },
        {
            onSuccess: () => {
                useDialogStore().openSuccessDialog(
                    "Auto-training model has been disabled."
                );
                emit("disabled");
            },
        }
    );
};

const updateAutoTrainingConfig = () => {
    if (!validate()) return;

    trainingStore.setAutoTraining(
        {
            autoTraining: true,
            reference: form.reference,
            nFactors: form.n_factors ? parseInt(form.n_factors) : null,
            nEpochs: form.n_epochs ? parseInt(form.n_epochs) : null,
            lrAll: form.lr_all ? parseFloat(form.lr_all) : null,
            regAll: form.reg_all ? parseFloat(form.reg_all) : null,
            autoTrainingInterval: parseFloat(form.auto_training_interval),
            intervalUnit: form.interval_unit,
        },
        {
            onSuccess: () => {
                useDialogStore().openSuccessDialog(
                    "Auto-training model configuration has been updated."
                );
                emit("updated");
            },
        }
    );
};
</script>

<template>
    <div class="flex flex-col gap-4 transition-all duration-300 ease-in-out">
        <h2 class="text-lg font-medium text-center text-gray-900">
            Auto Training Configuration
        </h2>

        <form @submit.prevent="">
            <div class="flex flex-col w-full gap-4 lg:flex-row lg:gap-6">
                <div class="flex flex-col w-full gap-4 sm:max-w-3xl">
                    <!-- Reference -->
                    <InputGroup id="auto_training_reference" label="Reference">
                        <div class="flex gap-2">
                            <Chip
                                v-for="(
                                    label, value
                                ) in trainingStore.references"
                                :key="value"
                                :label="label"
                                :selected="form.reference === value"
                                class="whitespace-nowrap"
                                @click="form.reference = value"
                            />
                        </div>

                        <template #suffix>
                            <InfoTooltip
                                id="auto-training-reference-info"
                                text="The type of recommendation reference used in training."
                            />
                        </template>
                    </InputGroup>

                    <div class="flex gap-4">
                        <!-- Auto Training Interval -->
                        <InputGroup
                            id="auto_training_interval"
                            label="Interval"
                        >
                            <TextInput
                                id="auto_training_interval"
                                v-model="form.auto_training_interval"
                                type="number"
                                placeholder="60"
                                :step="
                                    Number.isInteger(
                                        Number(form.auto_training_interval)
                                    )
                                        ? 1
                                        : 0.1
                                "
                                :error="form.errors.auto_training_interval"
                                @update:modelValue="
                                    form.errors.auto_training_interval = null
                                "
                                :bgClass="
                                    form.auto_training_interval
                                        ? 'border-gray-400'
                                        : ''
                                "
                            />
                            <template #suffix>
                                <InfoTooltip
                                    id="auto-training-interval-info"
                                    text="Interval in seconds for automatically retraining the model whenever there is a data change."
                                />
                            </template>
                        </InputGroup>

                        <!-- Interval Unit -->
                        <InputGroup
                            id="auto_training_interval_unit"
                            label="Interval Unit"
                        >
                            <DropdownSearchInput
                                id="auto_training_interval_unit"
                                :modelValue="{
                                    value: form.interval_unit,
                                    label: intervalUnits.find(
                                        (i) => i.value == form.interval_unit
                                    ).label,
                                }"
                                :options="intervalUnits"
                                option-label="label"
                                option-value="value"
                                placeholder="Select Interval Unit"
                                :error="form.errors.interval_unit"
                                @update:modelValue="
                                    form.errors.interval_unit = null;
                                    form.interval_unit = $event.value;
                                "
                                :bgClass="
                                    form.interval_unit ? 'border-gray-400' : ''
                                "
                            />
                            <template #suffix>
                                <InfoTooltip
                                    id="auto-training-interval-unit-info"
                                    text="The unit of time for the auto training interval."
                                />
                            </template>
                        </InputGroup>
                    </div>

                    <h3 class="text-md font-semibold text-start mt-2">
                        Hyperparameters
                    </h3>

                    <div class="flex gap-4">
                        <!-- Factors-->
                        <InputGroup
                            id="auto_training_n_factors"
                            label="Factors"
                        >
                            <TextInput
                                id="auto_training_n_factors"
                                v-model="form.n_factors"
                                type="number"
                                placeholder="100"
                                :error="form.errors.n_factors"
                                @update:modelValue="
                                    form.errors.n_factors = null
                                "
                                :bgClass="
                                    form.n_factors ? 'border-gray-400' : ''
                                "
                            />
                            <template #suffix>
                                <InfoTooltip
                                    id="auto-training-n-factors-info"
                                    text="Number of latent factors used in the model."
                                />
                            </template>
                        </InputGroup>

                        <!-- Epochs -->
                        <InputGroup id="auto_training_n_epochs" label="Epochs">
                            <TextInput
                                id="auto_training_n_epochs"
                                v-model="form.n_epochs"
                                type="number"
                                placeholder="20"
                                :error="form.errors.n_epochs"
                                @update:modelValue="form.errors.n_epochs = null"
                                :bgClass="
                                    form.n_epochs ? 'border-gray-400' : ''
                                "
                            />
                            <template #suffix>
                                <InfoTooltip
                                    id="auto-training-n-epochs-info"
                                    text="Number of training iterations."
                                />
                            </template>
                        </InputGroup>
                    </div>

                    <div class="flex gap-4">
                        <!-- Learning Rate -->
                        <InputGroup
                            id="auto_training_lr_all"
                            label="Learning Rate"
                        >
                            <TextInput
                                id="auto_training_lr_all"
                                v-model="form.lr_all"
                                type="number"
                                step="0.001"
                                placeholder="0.005"
                                :error="form.errors.lr_all"
                                @update:modelValue="form.errors.lr_all = null"
                                :bgClass="form.lr_all ? 'border-gray-400' : ''"
                            />
                            <template #suffix>
                                <InfoTooltip
                                    id="auto-training-lr-all-info"
                                    text="The learning rate for optimization."
                                />
                            </template>
                        </InputGroup>

                        <!-- Regularization -->
                        <InputGroup
                            id="auto_training_reg_all"
                            label="Regularization"
                        >
                            <TextInput
                                id="auto_training_reg_all"
                                v-model="form.reg_all"
                                type="number"
                                step="0.001"
                                placeholder="0.02"
                                :error="form.errors.reg_all"
                                @update:modelValue="form.errors.reg_all = null"
                                :bgClass="form.reg_all ? 'border-gray-400' : ''"
                            />
                            <template #suffix>
                                <InfoTooltip
                                    id="auto-training-reg-all-info"
                                    text="Regularization term to prevent overfitting."
                                />
                            </template>
                        </InputGroup>
                    </div>
                </div>
            </div>
        </form>

        <div class="mt-2 flex gap-4 justify-center">
            <SecondaryButton
                v-if="isActive"
                @click="disableAutoTrainingModel()"
                class="w-full"
            >
                Disable
                <template #prefix>
                    <svg
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
            </SecondaryButton>
            <PrimaryButton
                v-if="isActive"
                @click="updateAutoTrainingConfig()"
                class="w-full"
            >
                Update Config
                <template #prefix>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        class="size-5"
                    >
                        <path
                            d="M19.9998 7L20.9398 4.94L22.9998 4L20.9398 3.06L19.9998 1L19.0598 3.06L16.9998 4L19.0598 4.94L19.9998 7ZM8.49984 7L9.43984 4.94L11.4998 4L9.43984 3.06L8.49984 1L7.55984 3.06L5.49984 4L7.55984 4.94L8.49984 7ZM19.9998 12.5L19.0598 14.56L16.9998 15.5L19.0598 16.44L19.9998 18.5L20.9398 16.44L22.9998 15.5L20.9398 14.56L19.9998 12.5ZM18.4098 9.83L14.1698 5.59L1.58984 18.17L5.82984 22.41L18.4098 9.83ZM14.2098 11.21L12.7998 9.8L14.1798 8.42L15.5898 9.83L14.2098 11.21Z"
                            fill="currentColor"
                        />
                    </svg>
                </template>
            </PrimaryButton>
            <PrimaryButton
                v-if="!isActive"
                @click="enableAutoTrainingModel()"
                class="w-full"
            >
                Enable
                <template #prefix>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        class="size-5"
                    >
                        <path
                            d="M19.9998 7L20.9398 4.94L22.9998 4L20.9398 3.06L19.9998 1L19.0598 3.06L16.9998 4L19.0598 4.94L19.9998 7ZM8.49984 7L9.43984 4.94L11.4998 4L9.43984 3.06L8.49984 1L7.55984 3.06L5.49984 4L7.55984 4.94L8.49984 7ZM19.9998 12.5L19.0598 14.56L16.9998 15.5L19.0598 16.44L19.9998 18.5L20.9398 16.44L22.9998 15.5L20.9398 14.56L19.9998 12.5ZM18.4098 9.83L14.1698 5.59L1.58984 18.17L5.82984 22.41L18.4098 9.83ZM14.2098 11.21L12.7998 9.8L14.1798 8.42L15.5898 9.83L14.2098 11.21Z"
                            fill="currentColor"
                        />
                    </svg>
                </template>
            </PrimaryButton>
        </div>
    </div>
</template>
