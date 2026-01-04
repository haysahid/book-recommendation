<script setup lang="ts">
import { useTrainingStore } from "@/stores/training-store";
import ModelResultStatsLabel from "./ModelResultStatsLabel.vue";

const props = defineProps({
    model: {
        type: Object as () => ModelEntity,
        required: true,
    },
    previousModel: {
        type: Object as () => ModelEntity | null,
        required: false,
        default: null,
    },
});

const trainingStore = useTrainingStore();
</script>

<template>
    <div
        class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-4 items-center justify-center w-full p-2 bg-gray-50 rounded-md border border-gray-200"
    >
        <!-- Reference -->
        <ModelResultStatsLabel
            label="Reference"
            :value="trainingStore.references[props.model.reference]"
            :isHigher="false"
            :isLower="false"
        >
            <template #content>
                <p
                    class="font-semibold text-lg"
                    :class="{
                        'text-orange-700': props.model.reference === 'rating',
                        'text-indigo-700':
                            props.model.reference === 'transaction',
                    }"
                >
                    {{
                        trainingStore.references[props.model?.reference] ?? "-"
                    }}
                </p>
            </template>
        </ModelResultStatsLabel>

        <ModelResultStatsLabel
            label="Factors"
            :value="props.model.n_factors.toString()"
            :isHigher="
                props.previousModel
                    ? props.model.n_factors >
                      (props.previousModel ? props.previousModel.n_factors : 0)
                    : false
            "
            :isLower="
                props.previousModel
                    ? props.model.n_factors <
                      (props.previousModel ? props.previousModel.n_factors : 0)
                    : false
            "
        />
        <ModelResultStatsLabel
            label="Epochs"
            :value="props.model.n_epochs.toString()"
            :isHigher="
                props.previousModel
                    ? props.model.n_epochs >
                      (props.previousModel ? props.previousModel.n_epochs : 0)
                    : false
            "
            :isLower="
                props.previousModel
                    ? props.model.n_epochs <
                      (props.previousModel ? props.previousModel.n_epochs : 0)
                    : false
            "
        />
        <ModelResultStatsLabel
            label="Learning Rate"
            :value="props.model.lr_all.toFixed(4)"
            :isHigher="
                props.previousModel
                    ? props.model.lr_all >
                      (props.previousModel ? props.previousModel.lr_all : 0)
                    : false
            "
            :isLower="
                props.previousModel
                    ? props.model.lr_all <
                      (props.previousModel ? props.previousModel.lr_all : 0)
                    : false
            "
        />
        <ModelResultStatsLabel
            label="Regularization"
            :value="props.model.reg_all.toFixed(4)"
            :isHigher="
                props.previousModel
                    ? props.model.reg_all >
                      (props.previousModel ? props.previousModel.reg_all : 0)
                    : false
            "
            :isLower="
                props.previousModel
                    ? props.model.reg_all <
                      (props.previousModel ? props.previousModel.reg_all : 0)
                    : false
            "
        />
        <ModelResultStatsLabel
            label="RMSE"
            :value="props.model.rmse.toFixed(4)"
            :isHigher="
                props.previousModel
                    ? props.model.rmse >
                      (props.previousModel
                          ? props.previousModel.rmse
                          : Infinity)
                    : false
            "
            :isLower="
                props.previousModel
                    ? props.model.rmse <
                      (props.previousModel
                          ? props.previousModel.rmse
                          : Infinity)
                    : false
            "
        />
        <ModelResultStatsLabel
            label="MAE"
            :value="props.model.mae.toFixed(4)"
            :isHigher="
                props.previousModel
                    ? props.model.mae >
                      (props.previousModel ? props.previousModel.mae : Infinity)
                    : false
            "
            :isLower="
                props.previousModel
                    ? props.model.mae <
                      (props.previousModel ? props.previousModel.mae : Infinity)
                    : false
            "
        />
    </div>
</template>
