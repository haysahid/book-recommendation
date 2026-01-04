import { defineStore } from "pinia";
import { ref, watch } from "vue";
import axios from "axios";
import cookieManager from "@/plugins/cookie-manager";
import { useDialogStore } from "./dialog-store";

export const useTrainingStore = defineStore("training", () => {
    const datasetSources = ["Database", "Upload Files"];
    const references = {
        rating: "Rating",
        transaction: "Transaction",
    };

    const form = ref(
        JSON.parse(localStorage.getItem("training_form")) || {
            dataset_source: "Database",
            reference: "rating",
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

    const startTraining = async ({ onSuccess = () => {} }) => {
        if (form.value.dataset_source === "Upload Files") {
            if (!form.value.books_file) {
                form.value.errors.books_file = "Books file is required.";
            } else {
                form.value.errors.books_file = null;
            }

            if (!form.value.transactions_file) {
                form.value.errors.transactions_file =
                    "Transactions file is required.";
            } else {
                form.value.errors.transactions_file = null;
            }

            if (
                form.value.errors.books_file ||
                form.value.errors.transactions_file
            ) {
                return;
            }
        }

        clearTrainingResult();

        trainModelStatus.value = "loading";
        trainedModel.value = null;
        trainModelError.value = null;

        try {
            const formData = new FormData();

            formData.append("dataset_source", form.value.dataset_source);
            formData.append("reference", form.value.reference);

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
                    onSuccess();
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

    const models = ref<ModelEntity[]>(
        JSON.parse(localStorage.getItem("model_history")) || []
    );
    const activeModel = ref(
        JSON.parse(localStorage.getItem("active_model")) || null
    );
    const getModelHistoryStatus = ref(
        JSON.parse(localStorage.getItem("get_model_history_status")) || null
    );

    watch(models, (newModels) => {
        localStorage.setItem("model_history", JSON.stringify(newModels));
    });

    watch(activeModel, (newActiveModel) => {
        localStorage.setItem("active_model", JSON.stringify(newActiveModel));
    });

    watch(getModelHistoryStatus, (newStatus) => {
        localStorage.setItem(
            "get_model_history_status",
            JSON.stringify(newStatus)
        );
    });

    async function getModelHistory() {
        getModelHistoryStatus.value = "loading";
        try {
            await axios
                .get("/api/admin/recommendation-system/model-history", {
                    headers: {
                        Authorization: `Bearer ${cookieManager.getItem(
                            "access_token"
                        )}`,
                    },
                })
                .then((response) => {
                    models.value = response.data.result;
                    getModelHistoryStatus.value = "success";
                })
                .catch((err) => {
                    getModelHistoryStatus.value = "error";
                });
        } catch (err) {
            getModelHistoryStatus.value = "error";
        }
    }

    async function getActiveModel() {
        try {
            await axios
                .get("/api/admin/recommendation-system/active-model", {
                    headers: {
                        Authorization: `Bearer ${cookieManager.getItem(
                            "access_token"
                        )}`,
                    },
                })
                .then((response) => {
                    activeModel.value = response.data.result.model;
                })
                .catch((err) => {
                    activeModel.value = null;
                });
            return activeModel.value;
        } catch (err) {
            return null;
        }
    }

    async function activateModel(modelId: number) {
        try {
            await axios
                .post(
                    `/api/admin/recommendation-system/activate-model/${modelId}`,
                    {},
                    {
                        headers: {
                            Authorization: `Bearer ${cookieManager.getItem(
                                "access_token"
                            )}`,
                        },
                    }
                )
                .then((response) => {
                    getActiveModel();
                })
                .catch((err) => {
                    useDialogStore().openErrorDialog(
                        "An error occurred while activating the model."
                    );
                });
        } catch (err) {
            useDialogStore().openErrorDialog(
                "An error occurred while activating the model."
            );
        }
    }

    async function deleteModel(modelId: number) {
        try {
            await axios
                .delete(
                    `/api/admin/recommendation-system/model-history/${modelId}`,
                    {
                        headers: {
                            Authorization: `Bearer ${cookieManager.getItem(
                                "access_token"
                            )}`,
                        },
                    }
                )
                .then(async (response) => {
                    await getModelHistory();
                    await getActiveModel();
                    useDialogStore().openSuccessDialog(
                        "Model deleted successfully."
                    );
                })
                .catch((err) => {
                    useDialogStore().openErrorDialog(
                        "An error occurred while deleting the model."
                    );
                });
        } catch (err) {
            useDialogStore().openErrorDialog(
                "An error occurred while deleting the model."
            );
        }
    }

    const clearTrainingResult = () => {
        trainedModel.value = null;
        trainModelStatus.value = null;
        trainModelError.value = null;
    };

    const clearErrors = () => {
        form.value.books_file = null;
        form.value.transactions_file = null;
        form.value.errors = {
            books_file: null,
            transactions_file: null,
            n_factors: null,
            n_epochs: null,
            lr_all: null,
            reg_all: null,
        };
    };

    return {
        datasetSources,
        references,
        form,
        trainModelStatus,
        trainedModel,
        trainModelError,
        startTraining,
        clearTrainingResult,
        clearErrors,
        models,
        activeModel,
        getModelHistoryStatus,
        getModelHistory,
        getActiveModel,
        activateModel,
        deleteModel,
    };
});
