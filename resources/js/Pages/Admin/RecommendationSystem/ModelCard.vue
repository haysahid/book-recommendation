<script setup lang="ts">
import Tooltip from "@/Components/Tooltip.vue";
import ModelStatsLabel from "./ModelStatsLabel.vue";
import Switch from "@/Components/Switch.vue";
import { ref } from "vue";
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";

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
const emit = defineEmits(["activate", "delete"]);

const showDeleteConfirmationModal = ref(false);
</script>

<template>
    <div
        :class="[
            'border rounded-lg p-4',
            isActive ? 'border-blue-500 bg-blue-50' : 'border-gray-200',
        ]"
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
                    @update:modelValue="emit('activate', model.id)"
                />
                <template #content> Activate this model </template>
            </Tooltip>
        </div>
        <p class="text-xs text-gray-600 mb-1">
            {{ model.algorithm }} -
            {{ $formatDate(model.created_at) }}
        </p>
        <div class="flex gap-4 justify-between items-end">
            <!-- Stats -->
            <div class="flex flex-wrap gap-1.5 sm:gap-2 mt-2">
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
                    label="Learning Rate"
                    :value="model.lr_all.toFixed(4)"
                    :isActive="props.isActive"
                />
                <ModelStatsLabel
                    label="Regularization"
                    :value="model.reg_all.toFixed(4)"
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
            <button
                class="whitespace-nowrap text-xs text-gray-400 hover:underline hover:text-red-600"
                @click="showDeleteConfirmationModal = true"
            >
                Delete
            </button>
        </div>

        <DeleteConfirmationDialog
            :show="showDeleteConfirmationModal"
            :title="`Delete Model <b>${props.model.filename}</b>?`"
            :description="`This action cannot be undone.`"
            @close="showDeleteConfirmationModal = false"
            @delete="$emit('delete', props.model.id)"
        />
    </div>
</template>
