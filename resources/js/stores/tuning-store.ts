import { defineStore } from "pinia";
import { ref, watch } from "vue";
import axios from "axios";
import cookieManager from "@/plugins/cookie-manager";

export const useTuningStore = defineStore("tuning", () => {
    const datasetSources = ["Database", "Upload Files"];

    const gridSize = ref(
        JSON.parse(localStorage.getItem("tuning_grid_size")) || 3
    );
    const nFactorsPlaceholder = ref([
        100, 200, 300, 400, 500, 600, 700, 800, 900, 1000,
    ]);
    const nEpochsPlaceholder = ref([
        20, 40, 60, 80, 100, 120, 140, 160, 180, 200,
    ]);
    const lrAllPlaceholder = ref([
        0.002, 0.005, 0.01, 0.02, 0.05, 0.1, 0.2, 0.3, 0.4, 0.5,
    ]);
    const regAllPlaceholder = ref([0.02, 0.05, 0.1, 0.2, 0.5, 1, 2, 3, 4, 5]);

    const form = ref(
        JSON.parse(localStorage.getItem("tuning_form")) || {
            dataset_source: "Database",
            books_file: null,
            transactions_file: null,
            n_factors: [],
            n_epochs: [],
            lr_all: [],
            reg_all: [],
            cv: null,
            n_jobs: null,
            errors: {
                books_file: null,
                transactions_file: null,
                n_factors: null,
                n_epochs: null,
                lr_all: null,
                reg_all: null,
                cv: null,
                n_jobs: null,
            },
        }
    );

    const result = ref(
        JSON.parse(localStorage.getItem("tuning_result")) || null
    );

    const tuneModelStatus = ref(
        JSON.parse(localStorage.getItem("tuning_model_status")) || null
    );
    const tuneModelError = ref(
        JSON.parse(localStorage.getItem("tuning_model_error")) || null
    );

    watch(gridSize, (newValue) => {
        localStorage.setItem("tuning_grid_size", JSON.stringify(newValue));
    });

    watch(
        form,
        (newValue) => {
            localStorage.setItem(
                "tuning_form",
                JSON.stringify({
                    ...newValue,
                    books_file: null,
                    transactions_file: null,
                })
            );
        },
        { deep: true }
    );

    watch(result, (newValue) => {
        localStorage.setItem("tuning_result", JSON.stringify(newValue));
    });

    watch(tuneModelStatus, (newValue) => {
        localStorage.setItem("tuning_model_status", JSON.stringify(newValue));
    });

    watch(tuneModelError, (newValue) => {
        localStorage.setItem("tuning_model_error", JSON.stringify(newValue));
    });

    const startTuning = async () => {
        clearTuningResult();

        tuneModelStatus.value = "loading";
        tuneModelError.value = null;

        try {
            const formData = new FormData();

            if (form.value.books_file) {
                formData.append("books_file", form.value.books_file);
            }
            if (form.value.transactions_file) {
                formData.append(
                    "transactions_file",
                    form.value.transactions_file
                );
            }
            // Helper to limit array by gridSize
            const limitByGridSize = (arr: any[]) =>
                arr
                    .filter((value) => value !== null && value !== "")
                    .slice(0, gridSize.value);

            if (form.value.n_factors.length > 0) {
                const n_factors = limitByGridSize(form.value.n_factors);
                n_factors.forEach((value, index) => {
                    formData.append(`n_factors[${index}]`, value);
                });
            }
            if (form.value.n_epochs.length > 0) {
                const n_epochs = limitByGridSize(form.value.n_epochs);
                n_epochs.forEach((value, index) => {
                    formData.append(`n_epochs[${index}]`, value);
                });
            }
            if (form.value.lr_all.length > 0) {
                const lr_all = limitByGridSize(form.value.lr_all);
                lr_all.forEach((value, index) => {
                    formData.append(`lr_all[${index}]`, value);
                });
            }
            if (form.value.reg_all.length > 0) {
                const reg_all = limitByGridSize(form.value.reg_all);
                reg_all.forEach((value, index) => {
                    formData.append(`reg_all[${index}]`, value);
                });
            }
            if (form.value.cv) {
                formData.append("cv", form.value.cv);
            }
            if (form.value.n_jobs) {
                formData.append("n_jobs", form.value.n_jobs);
            }

            await axios
                .post("/api/admin/recommendation-system/tune", formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                        Authorization: `Bearer ${cookieManager.getItem(
                            "access_token"
                        )}`,
                    },
                })
                .then((response) => {
                    tuneModelStatus.value = "success";
                    result.value = response.data.result;
                })
                .catch((err) => {
                    if (err.status === 422) {
                        const errors = err.response.data.errors;
                        for (const key in errors) {
                            if (errors[key]) {
                                if (key.includes(".")) {
                                    const parts = key.split(".");
                                    form.value.errors[parts[0]] =
                                        errors[key][0];
                                } else {
                                    form.value.errors[key] = errors[key][0];
                                }
                            } else {
                                form.value.errors[key] = null;
                            }
                        }
                    }

                    if (err.response && err.response.data.meta) {
                        tuneModelError.value =
                            err.response.data.meta.message || "Tuning failed.";
                    } else {
                        tuneModelError.value = "Tuning failed.";
                    }
                    tuneModelStatus.value = "error";
                });
        } catch (err) {
            tuneModelError.value = "An unexpected error occurred.";
            tuneModelStatus.value = "error";
        }
    };

    const clearTuningResult = () => {
        result.value = null;
        tuneModelStatus.value = null;
        tuneModelError.value = null;
    };

    return {
        datasetSources,
        gridSize,
        nFactorsPlaceholder,
        nEpochsPlaceholder,
        lrAllPlaceholder,
        regAllPlaceholder,
        form,
        result,
        tuneModelStatus,
        tuneModelError,
        startTuning,
        clearTuningResult,
    };
});
