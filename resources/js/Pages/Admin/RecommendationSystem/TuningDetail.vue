<script setup lang="ts">
import { ref, computed } from "vue";

const props = defineProps({
    result: {
        type: Object as () => Record<string, any>,
        required: true,
    },
});

const result = props.result;

const rows = computed(() =>
    result.params?.map((param, i) => ({
        n_factors: param.n_factors,
        n_epochs: param.n_epochs,
        lr_all: param.lr_all,
        reg_all: param.reg_all,
        mean_rmse: result.mean_test_rmse[i],
        std_rmse: result.std_test_rmse[i],
        rank_rmse: result.rank_test_rmse[i],
        mean_mae: result.mean_test_mae[i],
        std_mae: result.std_test_mae[i],
        rank_mae: result.rank_test_mae[i],
        mean_fit_time: result.mean_fit_time[i],
        mean_test_time: result.mean_test_time[i],
    }))
);
</script>

<template>
    <div class="w-full">
        <div class="flex gap-4 justify-between items-center mb-2">
            <h3 class="text-md font-semibold text-green-800">Tuning Details</h3>
            <div class="text-sm text-gray-600 self-end">
                Total Trials: <b>{{ rows.length }}</b>
            </div>
        </div>
        <div class="overflow-x-auto h-96">
            <table class="min-w-full text-sm border border-gray-200 rounded-lg">
                <thead class="bg-gray-100 sticky top-0 z-10">
                    <tr>
                        <th v-if="rows[0].n_factors" class="px-2 py-1 border">
                            n_factors
                        </th>
                        <th v-if="rows[0].n_epochs" class="px-2 py-1 border">
                            n_epochs
                        </th>
                        <th v-if="rows[0].lr_all" class="px-2 py-1 border">
                            lr_all
                        </th>
                        <th v-if="rows[0].reg_all" class="px-2 py-1 border">
                            reg_all
                        </th>
                        <th class="px-2 py-1 border">Mean RMSE</th>
                        <th class="px-2 py-1 border">Std RMSE</th>
                        <th class="px-2 py-1 border">Rank RMSE</th>
                        <th class="px-2 py-1 border">Mean MAE</th>
                        <th class="px-2 py-1 border">Std MAE</th>
                        <th class="px-2 py-1 border">Rank MAE</th>
                        <th class="px-2 py-1 border">Fit Time (s)</th>
                        <th class="px-2 py-1 border">Test Time (s)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(row, i) in rows"
                        :key="i"
                        :class="
                            row.rank_rmse === 1 ? 'bg-blue-100 font-bold' : ''
                        "
                        class="[&>td]:break-all"
                    >
                        <td
                            v-if="row.n_factors"
                            class="px-2 py-1 border text-center"
                        >
                            {{ row.n_factors ?? "-" }}
                        </td>
                        <td
                            v-if="row.n_epochs"
                            class="px-2 py-1 border text-center"
                        >
                            {{ row.n_epochs ?? "-" }}
                        </td>
                        <td
                            v-if="row.lr_all"
                            class="px-2 py-1 border text-center"
                        >
                            {{ row.lr_all ?? "-" }}
                        </td>
                        <td
                            v-if="row.reg_all"
                            class="px-2 py-1 border text-center"
                        >
                            {{ row.reg_all ?? "-" }}
                        </td>
                        <td class="px-2 py-1 border text-center">
                            {{ row.mean_rmse.toFixed(4) }}
                        </td>
                        <td class="px-2 py-1 border text-center">
                            {{ row.std_rmse.toFixed(4) }}
                        </td>
                        <td class="px-2 py-1 border text-center">
                            {{ row.rank_rmse }}
                        </td>
                        <td class="px-2 py-1 border text-center">
                            {{ row.mean_mae.toFixed(4) }}
                        </td>
                        <td class="px-2 py-1 border text-center">
                            {{ row.std_mae.toFixed(4) }}
                        </td>
                        <td class="px-2 py-1 border text-center">
                            {{ row.rank_mae }}
                        </td>
                        <td class="px-2 py-1 border text-center">
                            {{ row.mean_fit_time.toFixed(2) }}
                        </td>
                        <td class="px-2 py-1 border text-center">
                            {{ row.mean_test_time.toFixed(2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            <h3 class="font-semibold mb-1 text-md text-green-800">
                Keterangan:
            </h3>
            <ul class="list-disc ml-6 text-sm text-gray-700">
                <li>
                    <b>Mean RMSE</b>: Rata-rata Root Mean Squared Error pada
                    validasi.
                </li>
                <li><b>Std RMSE</b>: Standar deviasi RMSE pada validasi.</li>
                <li>
                    <b>Rank RMSE</b>: Ranking performa RMSE (semakin kecil
                    semakin baik).
                </li>
                <li>
                    <b>Mean MAE</b>: Rata-rata Mean Absolute Error pada
                    validasi.
                </li>
                <li><b>Std MAE</b>: Standar deviasi MAE pada validasi.</li>
                <li>
                    <b>Rank MAE</b>: Ranking performa MAE (semakin kecil semakin
                    baik).
                </li>
                <li>
                    <b>Fit Time</b>: Waktu rata-rata training per fold (detik).
                </li>
                <li>
                    <b>Test Time</b>: Waktu rata-rata evaluasi per fold (detik).
                </li>
                <li>
                    Baris berwarna
                    <span class="bg-blue-100 px-1">biru</span> menandakan
                    parameter dengan performa RMSE terbaik.
                </li>
            </ul>
        </div>
    </div>
</template>
