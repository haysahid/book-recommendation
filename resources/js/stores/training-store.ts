import { defineStore } from "pinia";
import { ref, watch } from "vue";
import axios from "axios";
import cookieManager from "@/plugins/cookie-manager";

export const useTrainingStore = defineStore("training", () => {
    const datasetSources = ["Database", "Upload Files"];

    const form = ref(
        JSON.parse(localStorage.getItem("training_form")) || {
            dataset_source: "Database",
            books_file: null,
            transactions_file: null,
            n_factors: null,
            n_epochs: null,
            lr_all: null,
            reg_all: null,
            errors: {
                books_file: null,
                transactions_file: null,
                n_factors: null,
                n_epochs: null,
                lr_all: null,
                reg_all: null,
            },
        }
    );

    const trainModelStatus = ref(
        JSON.parse(localStorage.getItem("training_model_status")) || null
    );
    const trainedModel = ref<ModelEntity | null>(
        JSON.parse(localStorage.getItem("trained_model")) || null
    );
    const trainModelError = ref(
        JSON.parse(localStorage.getItem("training_model_error")) || null
    );

    watch(
        () => form.value,
        (newForm) => {
            localStorage.setItem("training_form", JSON.stringify(newForm));
        },
        { deep: true }
    );

    watch(trainModelStatus, (newStatus) => {
        localStorage.setItem(
            "training_model_status",
            JSON.stringify(newStatus)
        );
    });

    watch(trainedModel, (newModel) => {
        localStorage.setItem("trained_model", JSON.stringify(newModel));
    });

    watch(trainModelError, (newError) => {
        localStorage.setItem("training_model_error", JSON.stringify(newError));
    });

    const startTraining = async () => {
        clearTrainingResult();

        trainModelStatus.value = "loading";
        trainedModel.value = null;
        trainModelError.value = null;

        await new Promise((resolve) => setTimeout(resolve, 1000));

        try {
            const formData = new FormData();

            formData.append("dataset_source", form.value.dataset_source);

            if (form.value.dataset_source === "Upload Files") {
                if (form.value.books_file) {
                    formData.append("books_file", form.value.books_file);
                }
                if (form.value.transactions_file) {
                    formData.append(
                        "transactions_file",
                        form.value.transactions_file
                    );
                }
            }

            if (form.value.n_factors && form.value.n_factors !== "") {
                formData.append("n_factors", form.value.n_factors);
            }
            if (form.value.n_epochs && form.value.n_epochs !== "") {
                formData.append("n_epochs", form.value.n_epochs);
            }
            if (form.value.lr_all && form.value.lr_all !== "") {
                formData.append("lr_all", form.value.lr_all);
            }
            if (form.value.reg_all && form.value.reg_all !== "") {
                formData.append("reg_all", form.value.reg_all);
            }

            await axios
                .post("/api/admin/recommendation-system/train", formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                        Authorization: `Bearer ${cookieManager.getItem(
                            "access_token"
                        )}`,
                    },
                })
                .then((response) => {
                    trainedModel.value = response.data.result.model;
                    trainModelStatus.value = "success";
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
                        trainModelError.value =
                            err.response.data.meta.message ||
                            "Training failed.";
                    } else {
                        trainModelError.value = "Training failed.";
                    }
                    trainModelStatus.value = "error";
                });
        } catch (err) {
            trainModelError.value = "An unexpected error occurred.";
            trainModelStatus.value = "error";
        }
    };

    const clearTrainingResult = () => {
        trainedModel.value = null;
        trainModelStatus.value = null;
        trainModelError.value = null;
    };

    return {
        datasetSources,
        form,
        trainModelStatus,
        trainedModel,
        trainModelError,
        startTraining,
        clearTrainingResult,
    };
});
