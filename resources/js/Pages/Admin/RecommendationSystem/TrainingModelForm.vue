<script setup lang="ts">
import InfoTooltip from "@/Components/InfoTooltip.vue";
import InputGroup from "@/Components/InputGroup.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import ModelResultStatsLabel from "./ModelResultStatsLabel.vue";
import Chip from "@/Components/Chip.vue";
import FileInput from "@/Components/FileInput.vue";
import { useTrainingStore } from "@/stores/training-store";

const props = defineProps({
    previousModel: {
        type: Object as () => ModelEntity | null,
        required: false,
        default: null,
    },
});

const emit = defineEmits(["modelTrained"]);

const trainingStore = useTrainingStore();
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
                @click="trainingStore.startTraining"
            >
                {{
                    trainingStore.trainModelStatus == "loading"
                        ? "Training..."
                        : "Start Training"
                }}
            </PrimaryButton>
        </div>

        <form @submit.prevent="trainingStore.startTraining">
            <div class="flex flex-col w-full gap-4 lg:flex-row lg:gap-6">
                <div class="flex flex-col w-full gap-4 sm:max-w-2xl">
                    <div
                        class="flex flex-col w-full gap-4 lg:flex-row lg:gap-6"
                    >
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
                    </div>

                    <template
                        v-if="
                            trainingStore.form.dataset_source == 'Upload Files'
                        "
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
                            />
                        </InputGroup>
                    </template>

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

                <div
                    class="rounded-lg py-4 px-6 bg-white flex flex-col xl:flex-row gap-4 items-center border border-green-400"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        class="size-9 shrink-0"
                    >
                        <g clip-path="url(#clip0_483_27)">
                            <path
                                d="M14.4001 3.41911C14.4451 3.29619 14.5268 3.19008 14.6341 3.1151C14.7415 3.04013 14.8692 2.99993 15.0001 2.99993C15.131 2.99993 15.2588 3.04013 15.3661 3.1151C15.4734 3.19008 15.5551 3.29619 15.6001 3.41911L16.2101 5.08711C16.6895 6.39482 17.4481 7.58241 18.4329 8.56728C19.4178 9.55216 20.6054 10.3107 21.9131 10.7901L23.5811 11.4001C23.704 11.4451 23.8101 11.5268 23.8851 11.6341C23.9601 11.7415 24.0003 11.8692 24.0003 12.0001C24.0003 12.131 23.9601 12.2588 23.8851 12.3661C23.8101 12.4734 23.704 12.5551 23.5811 12.6001L21.9131 13.2101C20.6054 13.6895 19.4178 14.4481 18.4329 15.4329C17.4481 16.4178 16.6895 17.6054 16.2101 18.9131L15.6001 20.5811C15.5551 20.704 15.4734 20.8101 15.3661 20.8851C15.2588 20.9601 15.131 21.0003 15.0001 21.0003C14.8692 21.0003 14.7415 20.9601 14.6341 20.8851C14.5268 20.8101 14.4451 20.704 14.4001 20.5811L13.7901 18.9131C13.3107 17.6054 12.5522 16.4178 11.5673 15.4329C10.5824 14.4481 9.39482 13.6895 8.08711 13.2101L6.41911 12.6001C6.29619 12.5551 6.19008 12.4734 6.1151 12.3661C6.04013 12.2588 5.99993 12.131 5.99993 12.0001C5.99993 11.8692 6.04013 11.7415 6.1151 11.6341C6.19008 11.5268 6.29619 11.4451 6.41911 11.4001L8.08711 10.7901C9.39482 10.3107 10.5824 9.55216 11.5673 8.56728C12.5522 7.58241 13.3107 6.39482 13.7901 5.08711L14.4001 3.41911ZM8.00011 16.6751C8.01876 16.6238 8.05276 16.5794 8.09749 16.5481C8.14221 16.5168 8.1955 16.5 8.25011 16.5C8.30472 16.5 8.358 16.5168 8.40273 16.5481C8.44746 16.5794 8.48146 16.6238 8.50011 16.6751L8.75411 17.3691C8.954 17.914 9.27006 18.4088 9.68035 18.8193C10.0906 19.2297 10.5853 19.546 11.1301 19.7461L11.8251 20.0001C11.8764 20.0188 11.9208 20.0528 11.9521 20.0975C11.9834 20.1422 12.0002 20.1955 12.0002 20.2501C12.0002 20.3047 11.9834 20.358 11.9521 20.4027C11.9208 20.4475 11.8764 20.4815 11.8251 20.5001L11.1301 20.7541C10.5853 20.9542 10.0906 21.2705 9.68035 21.681C9.27006 22.0914 8.954 22.5863 8.75411 23.1311L8.50011 23.8251C8.48146 23.8764 8.44746 23.9208 8.40273 23.9521C8.358 23.9834 8.30472 24.0002 8.25011 24.0002C8.1955 24.0002 8.14221 23.9834 8.09749 23.9521C8.05276 23.9208 8.01876 23.8764 8.00011 23.8251L7.74611 23.1311C7.54622 22.5863 7.23016 22.0914 6.81987 21.681C6.40958 21.2705 5.91488 20.9542 5.37011 20.7541L4.67511 20.5001C4.62379 20.4815 4.57945 20.4475 4.54811 20.4027C4.51678 20.358 4.49997 20.3047 4.49997 20.2501C4.49997 20.1955 4.51678 20.1422 4.54811 20.0975C4.57945 20.0528 4.62379 20.0188 4.67511 20.0001L5.37011 19.7461C5.91488 19.546 6.40958 19.2297 6.81987 18.8193C7.23016 18.4088 7.54622 17.914 7.74611 17.3691L8.00011 16.6751ZM4.20011 0.21011C4.22284 0.148869 4.26377 0.096052 4.3174 0.0587543C4.37103 0.0214566 4.43479 0.00146484 4.50011 0.00146484C4.56543 0.00146484 4.62919 0.0214566 4.68282 0.0587543C4.73645 0.096052 4.77738 0.148869 4.80011 0.21011L5.10511 1.04311C5.34511 1.69694 5.72453 2.29071 6.21702 2.7832C6.70951 3.27569 7.30328 3.65511 7.95711 3.89511L8.79011 4.20011C8.85135 4.22284 8.90417 4.26377 8.94147 4.3174C8.97876 4.37103 8.99875 4.43479 8.99875 4.50011C8.99875 4.56543 8.97876 4.62919 8.94147 4.68282C8.90417 4.73645 8.85135 4.77738 8.79011 4.80011L7.95711 5.10511C7.30328 5.34511 6.70951 5.72453 6.21702 6.21702C5.72453 6.70951 5.34511 7.30328 5.10511 7.95711L4.80011 8.79011C4.77738 8.85135 4.73645 8.90417 4.68282 8.94147C4.62919 8.97876 4.56543 8.99875 4.50011 8.99875C4.43479 8.99875 4.37103 8.97876 4.3174 8.94147C4.26377 8.90417 4.22284 8.85135 4.20011 8.79011L3.89511 7.95711C3.65511 7.30328 3.27569 6.70951 2.7832 6.21702C2.29071 5.72453 1.69694 5.34511 1.04311 5.10511L0.21011 4.80011C0.148869 4.77738 0.096052 4.73645 0.0587543 4.68282C0.0214566 4.62919 0.00146484 4.56543 0.00146484 4.50011C0.00146484 4.43479 0.0214566 4.37103 0.0587543 4.3174C0.096052 4.26377 0.148869 4.22284 0.21011 4.20011L1.04311 3.89511C1.69694 3.65511 2.29071 3.27569 2.7832 2.7832C3.27569 2.29071 3.65511 1.69694 3.89511 1.04311L4.20011 0.21011Z"
                                fill="currentColor"
                            />
                        </g>
                        <defs>
                            <clipPath id="clip0_483_27">
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
                                {{ trainingStore.trainedModel.filename }}
                            </p>
                            <p class="text-sm text-gray-600 mb-1">
                                {{ trainingStore.trainedModel.algorithm }} -
                                {{
                                    $formatDate(
                                        trainingStore.trainedModel.created_at
                                    )
                                }}
                            </p>
                        </div>

                        <!-- Stats -->
                        <div
                            class="flex flex-row max-xl:flex-wrap gap-4 items-center justify-center"
                        >
                            <ModelResultStatsLabel
                                label="Factors"
                                :value="
                                    trainingStore.trainedModel.n_factors.toString()
                                "
                                :isHigher="
                                    props.previousModel
                                        ? trainingStore.trainedModel.n_factors >
                                          (props.previousModel
                                              ? props.previousModel.n_factors
                                              : 0)
                                        : false
                                "
                                :isLower="
                                    props.previousModel
                                        ? trainingStore.trainedModel.n_factors <
                                          (props.previousModel
                                              ? props.previousModel.n_factors
                                              : 0)
                                        : false
                                "
                            />
                            <ModelResultStatsLabel
                                label="Epochs"
                                :value="
                                    trainingStore.trainedModel.n_epochs.toString()
                                "
                                :isHigher="
                                    props.previousModel
                                        ? trainingStore.trainedModel.n_epochs >
                                          (props.previousModel
                                              ? props.previousModel.n_epochs
                                              : 0)
                                        : false
                                "
                                :isLower="
                                    props.previousModel
                                        ? trainingStore.trainedModel.n_epochs <
                                          (props.previousModel
                                              ? props.previousModel.n_epochs
                                              : 0)
                                        : false
                                "
                            />
                            <ModelResultStatsLabel
                                label="Learning Rate"
                                :value="
                                    trainingStore.trainedModel.lr_all.toFixed(4)
                                "
                                :isHigher="
                                    props.previousModel
                                        ? trainingStore.trainedModel.lr_all >
                                          (props.previousModel
                                              ? props.previousModel.lr_all
                                              : 0)
                                        : false
                                "
                                :isLower="
                                    props.previousModel
                                        ? trainingStore.trainedModel.lr_all <
                                          (props.previousModel
                                              ? props.previousModel.lr_all
                                              : 0)
                                        : false
                                "
                            />
                            <ModelResultStatsLabel
                                label="Regularization"
                                :value="
                                    trainingStore.trainedModel.reg_all.toFixed(
                                        4
                                    )
                                "
                                :isHigher="
                                    props.previousModel
                                        ? trainingStore.trainedModel.reg_all >
                                          (props.previousModel
                                              ? props.previousModel.reg_all
                                              : 0)
                                        : false
                                "
                                :isLower="
                                    props.previousModel
                                        ? trainingStore.trainedModel.reg_all <
                                          (props.previousModel
                                              ? props.previousModel.reg_all
                                              : 0)
                                        : false
                                "
                            />
                            <ModelResultStatsLabel
                                label="RMSE"
                                :value="
                                    trainingStore.trainedModel.rmse.toFixed(4)
                                "
                                :isHigher="
                                    props.previousModel
                                        ? trainingStore.trainedModel.rmse >
                                          (props.previousModel
                                              ? props.previousModel.rmse
                                              : Infinity)
                                        : false
                                "
                                :isLower="
                                    props.previousModel
                                        ? trainingStore.trainedModel.rmse <
                                          (props.previousModel
                                              ? props.previousModel.rmse
                                              : Infinity)
                                        : false
                                "
                            />
                            <ModelResultStatsLabel
                                label="MAE"
                                :value="
                                    trainingStore.trainedModel.mae.toFixed(4)
                                "
                                :isHigher="
                                    props.previousModel
                                        ? trainingStore.trainedModel.mae >
                                          (props.previousModel
                                              ? props.previousModel.mae
                                              : Infinity)
                                        : false
                                "
                                :isLower="
                                    props.previousModel
                                        ? trainingStore.trainedModel.mae <
                                          (props.previousModel
                                              ? props.previousModel.mae
                                              : Infinity)
                                        : false
                                "
                            />
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
