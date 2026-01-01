<script setup lang="ts">
import Tooltip from "@/Components/Tooltip.vue";
import ModelStatsLabel from "./ModelStatsLabel.vue";
import Switch from "@/Components/Switch.vue";

const props = defineProps({
    model: {
        type: Object as () => ModelEntity,
        required: true,
    },
    isActive: {
        type: Boolean,
        required: false,
        default: false,
    },
});
const emit = defineEmits(["activate"]);
</script>

<template>
    <div
        :class="[
            'border rounded-lg p-4',
            isActive ? 'border-blue-500 bg-blue-50' : 'border-gray-200',
        ]"
        @click="$emit('activate', model.id)"
    >
        <div class="flex gap-4 justify-between">
            <p
                class="text-md font-semibold mb-2 text-ellipsis overflow-hidden whitespace-nowrap"
            >
                {{ model.filename }}
            </p>
            <div
                v-if="props.isActive"
                class="inline-block px-2 py-1 text-xs font-medium text-white bg-green-500 rounded-full mb-2"
            >
                Active
            </div>
            <Tooltip
                v-else
                :id="`activate-model-tooltip-${model.id}`"
                placement="bottom"
            >
                <Switch
                    :modelValue="false"
                    @change="$emit('activate', model.id)"
                />

                <template #content> Activate this model </template>
            </Tooltip>
        </div>
        <p class="text-xs text-gray-600 mb-1">
            {{ model.algorithm }} -
            {{ $formatDate(model.created_at) }}
        </p>
        <!-- Stats -->
        <div class="flex flex-wrap gap-4 mt-2">
            <ModelStatsLabel
                label="Factors"
                :value="model.n_factors.toString()"
                :isActive="props.isActive"
            />
            <ModelStatsLabel
                label="Epochs"
                :value="model.n_epochs.toString()"
                :isActive="props.isActive"
            />
            <ModelStatsLabel
                label="RMSE"
                :value="model.rmse.toFixed(4)"
                :isActive="props.isActive"
            />
            <ModelStatsLabel
                label="MAE"
                :value="model.mae.toFixed(4)"
                :isActive="props.isActive"
            />
        </div>
    </div>
</template>
