<script setup lang="ts">
import { useTrainingStore } from "@/stores/training-store";
import ModelResultStatsLabel from "./ModelResultStatsLabel.vue";

const props = defineProps({
    model: {
        type: Object as () => ModelEntity,
        required: true,
    },
    compareModel: {
        type: Object as () => ModelEntity,
        required: false,
        default: null,
    },
    showSubTitles: {
        type: Boolean,
        required: false,
        default: false,
    },
    note: {
        type: String,
        required: false,
        default: null,
    },
});

const trainingStore = useTrainingStore();
</script>

<template>
    <div class="flex flex-row gap-4 items-start">
        <div class="w-full lg:w-[66%]">
            <h3 v-if="props.showSubTitles" class="text-md font-semibold mb-2">
                Best Hyperparameters
            </h3>
            <div
                class="bg-gray-50 w-full rounded-md border border-gray-200 p-2"
            >
                <div
                    class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-5 gap-2 justify-start"
                >
                    <ModelResultStatsLabel
                        label="Reference"
                        :value="props.model?.reference ?? '-'"
                        :isHigher="false"
                        :isLower="false"
                        class="items-start!"
                    >
                        <template #content>
                            <p
                                class="font-semibold text-lg"
                                :class="{
                                    'text-orange-700':
                                        props.model.reference === 'rating',
                                    'text-indigo-700':
                                        props.model.reference === 'transaction',
                                }"
                            >
                                {{
                                    trainingStore.references[
                                        props.model?.reference
                                    ] ?? "-"
                                }}
                            </p>
                        </template>
                    </ModelResultStatsLabel>
                    <ModelResultStatsLabel
                        label="Factors"
                        :value="props.model?.n_factors?.toString() ?? '-'"
                        :isHigher="props.compareModel
                                        ? props.model
                                              ?.n_factors! >
                                          props.compareModel.n_factors
                                        : false"
                        :isLower="
                                        props.compareModel
                                            ? props.model
                                                  ?.n_factors! <
                                              props.compareModel.n_factors
                                            : false"
                        class="items-start!"
                    />
                    <ModelResultStatsLabel
                        label="Epochs"
                        :value="props.model?.n_epochs?.toString() ?? '-'"
                        :isHigher="
                                        props.compareModel
                                            ? props.model
                                                  ?.n_epochs! >
                                              props.compareModel.n_epochs
                                            : false"
                        :isLower="
                                        props.compareModel
                                            ? props.model
                                                  ?.n_epochs! <
                                              props.compareModel.n_epochs
                                            : false"
                        class="items-start!"
                    />
                    <ModelResultStatsLabel
                        label="Learning Rate"
                        :value="props.model?.lr_all?.toString() ?? '-'"
                        :isHigher="
                                        props.compareModel
                                            ? props.model
                                                  ?.lr_all! >
                                              props.compareModel.lr_all
                                            : false"
                        :isLower="
                                        props.compareModel
                                            ? props.model
                                                  ?.lr_all! <
                                              props.compareModel.lr_all
                                            : false"
                        class="items-start!"
                    />
                    <ModelResultStatsLabel
                        label="Regularization"
                        :value="props.model?.reg_all?.toString() ?? '-'"
                        :isHigher="
                                        props.compareModel
                                            ? props.model
                                                  ?.reg_all! >
                                              props.compareModel.reg_all
                                            : false"
                        :isLower="
                                        props.compareModel
                                            ? props.model
                                                  ?.reg_all! <
                                              props.compareModel.reg_all
                                            : false"
                        class="items-start!"
                    />
                </div>

                <p v-if="props.note" class="text-xs px-4 w-full">
                    {{ props.note }}
                </p>
            </div>
        </div>

        <div class="w-full lg:w-[34%]">
            <h3 v-if="props.showSubTitles" class="text-md font-semibold mb-2">
                Best Scores
            </h3>
            <div
                class="bg-gray-50 w-full rounded-md border border-gray-200 p-2"
            >
                <div
                    class="grid grid-cols-1 xl:grid-cols-3 gap-2 justify-start"
                >
                    <ModelResultStatsLabel
                        label="RMSE Score"
                        :value="props.model.rmse.toFixed(4)"
                        :isHigher="
                            props.compareModel
                                ? props.model.rmse > props.compareModel.rmse
                                : false
                        "
                        :isLower="
                            props.compareModel
                                ? props.model.rmse < props.compareModel.rmse
                                : false
                        "
                        class="items-start!"
                    />
                    <ModelResultStatsLabel
                        label="MAE Score"
                        :value="props.model.mae.toFixed(4)"
                        :isHigher="
                            props.compareModel
                                ? props.model.mae > props.compareModel.mae
                                : false
                        "
                        :isLower="
                            props.compareModel
                                ? props.model.mae < props.compareModel.mae
                                : false
                        "
                        class="items-start!"
                    />
                    <ModelResultStatsLabel
                        label="Scale Range"
                        :value="
                            props.model.min_value && props.model.max_value
                                ? `${props.model.min_value} - ${props.model.max_value}`
                                : '-'
                        "
                        :isHigher="false"
                        :isLower="false"
                        class="items-start!"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
