import { useDialogStore } from "@/stores/dialog-store";
import axios from "axios";
import { router } from "@inertiajs/vue3";

export default function useBookService() {
    const dialogStore = useDialogStore();

    async function loadCategories(
        {} = {},
        {
            autoShowDialog = false,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");
        await axios
            .get(
                "https://api-service.gramedia.com/api/v2/public/subcategory?parent_category=buku"
            )
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            "Data kategori berhasil diambil."
                    );
                }
            })
            .catch((error) => {
                console.error("Error fetching categories:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat mengambil data kategori."
                    );
                }
            });
    }

    async function saveCategories(
        categories: CategoryModel[],
        {
            autoShowDialog = false,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");

        const formData = new FormData();
        formData.append("categories", JSON.stringify(categories));

        router.post("/admin/categories", formData, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.props.flash?.success ||
                            "Data kategori berhasil disimpan."
                    );
                }
            },
            onError: (errors) => {
                console.error("Error saving categories:", errors);
                onChangeStatus("error");
                onError(errors);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        "Terjadi kesalahan saat menyimpan data kategori."
                    );
                }
            },
        });
    }

    async function getBooks(
        {
            page = undefined,
            search = undefined,
            limit = undefined,
            orderBy = undefined,
        } = {},
        {
            autoShowDialog = false,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");
        await axios
            .get(
                "https://api-service.gramedia.com/api/v2/public/products?slug=buku",
                {
                    params: {
                        page,
                        search,
                        limit,
                        order_by: orderBy,
                    },
                }
            )
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            "Data pengguna berhasil diambil."
                    );
                }
            })
            .catch((error) => {
                console.error("Error fetching users:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat mengambil data pengguna."
                    );
                }
            });
    }

    return {
        loadCategories,
        saveCategories,
        getBooks,
    };
}
