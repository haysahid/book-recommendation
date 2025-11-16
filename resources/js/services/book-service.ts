import { useDialogStore } from "@/stores/dialog-store";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import cookieManager from "@/plugins/cookie-manager";

export default function useBookService() {
    const dialogStore = useDialogStore();
    const token = `Bearer ${cookieManager.getItem("access_token")}`;
    const selectedStoreId = cookieManager.getItem("selected_store_id");

    async function loadCategories(
        {} = {},
        {
            autoShowDialog = false,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        const endpointUrl =
            "https://api-service.gramedia.com/api/v2/public/subcategory?parent_category=buku";
        const url = new URL(endpointUrl);
        url.searchParams.sort();
        const cacheKey = url.toString();

        const cachedData = localStorage.getItem(cacheKey);

        if (cachedData) {
            const categories = JSON.parse(cachedData);
            onChangeStatus("success");
            onSuccess({ data: { data: categories } });
            return;
        }

        onChangeStatus("loading");
        await axios
            .get(endpointUrl)
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            "Data kategori berhasil diambil."
                    );
                }

                // Cache request data based on url and params
                localStorage.setItem(
                    cacheKey,
                    JSON.stringify(response.data.data)
                );
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

        // Save categories per 20 items
        const chunkSize = 20;

        for (let i = 0; i < categories.length; i += chunkSize) {
            const chunk = categories.slice(i, i + chunkSize);

            await axios
                .post(
                    "/api/admin/categories",
                    {
                        categories: chunk,
                    },
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                            Authorization: token,
                        },
                    }
                )
                .then((response) => {
                    onChangeStatus("success");
                    onSuccess(response);
                    if (autoShowDialog) {
                        dialogStore.openSuccessDialog(
                            response.data.meta.message ||
                                "Data kategori berhasil disimpan."
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error saving categories:", error);
                    onChangeStatus("error");
                    onError(error);
                    if (autoShowDialog) {
                        dialogStore.openErrorDialog(
                            error.response?.data?.meta?.message ||
                                "Terjadi kesalahan saat menyimpan data kategori."
                        );
                    }
                });
        }
    }

    async function loadBooks(
        {
            page = undefined,
            search = undefined,
            limit = undefined,
            category = undefined,
        } = {},
        {
            autoShowDialog = false,
            onSuccess = (
                books: BookEntity[],
                totalData?: number,
                isFromCache: boolean = false
            ) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        // Example:
        // const endpointUrl = "https://api-service.gramedia.com/api/v2/public/products?page=1&pageSize=28&size=28&slug=buku&slug=agama";

        const slugs = category.slug.split("/");

        const endpointUrl = `https://api-service.gramedia.com/api/v2/public/products?page=${page}&pageSize=${limit}&size=${limit}&slug=${slugs.join(
            "&slug="
        )}`;
        const url = new URL(endpointUrl);
        url.searchParams.sort();
        const cacheKey = url.toString();

        const cachedData = localStorage.getItem(cacheKey);

        const categoryCache = JSON.parse(
            localStorage.getItem("category_books_count") || "{}"
        );

        if (cachedData) {
            const books = JSON.parse(cachedData);
            if (
                categoryCache[category.slug] === undefined ||
                categoryCache[category.slug] === 0
            ) {
                // Get total data from API to cache
                await axios
                    .get(endpointUrl, {})
                    .then((response) => {
                        // Cache category data
                        categoryCache[category.slug] =
                            response.data.meta.total_data;
                        localStorage.setItem(
                            "category_books_count",
                            JSON.stringify(categoryCache)
                        );
                    })
                    .catch((error) => {
                        console.error(
                            "Error fetching users for category cache:",
                            error
                        );
                    });
            }

            const totalData = categoryCache[category.slug];

            onChangeStatus("success");
            onSuccess(books, totalData, true);
            return;
        }

        onChangeStatus("loading");
        await axios
            .get(endpointUrl, {})
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response.data.data, response.data.meta.total_data);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            "Data pengguna berhasil diambil."
                    );
                }

                // Cache request data based on url and params
                localStorage.setItem(
                    cacheKey,
                    JSON.stringify(response.data.data)
                );

                // Cache category data
                categoryCache[category.slug] = response.data.meta.total_data;
                localStorage.setItem(
                    "category_books_count",
                    JSON.stringify(categoryCache)
                );
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

    async function saveBooks(
        category: CategoryModel,
        {
            autoShowDialog = false,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");

        let books: BookEntity[] = [];

        // Get cached books
        // Get all localStorage keys that contain category slug
        for (const key of Object.keys(localStorage)) {
            if (
                key.startsWith(
                    "https://api-service.gramedia.com/api/v2/public/products"
                ) === false
            ) {
                continue;
            }

            const url = new URL(key);
            // get all slug params from url
            const slugs = url.searchParams.getAll("slug");
            if (
                slugs.every((slug) => category.slug.split("/").includes(slug))
            ) {
                books = [
                    ...books,
                    ...(
                        JSON.parse(
                            localStorage.getItem(key) || "[]"
                        ) as BookEntity[]
                    ).map((book) => ({
                        ...book,
                        category_slug: category.slug,
                    })),
                ];
            }
        }

        // Save books per 20 items
        const chunkSize = 20;
        const errorCodes: string[] = [];

        for (let i = 0; i < books.length; i += chunkSize) {
            const chunk = books.slice(i, i + chunkSize);

            console.log("Saving books chunk:", chunk);

            await axios
                .post(
                    "/api/admin/books",
                    {
                        books: chunk,
                    },
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                            Authorization: token,
                        },
                    }
                )
                .then((response) => {
                    onChangeStatus("success");
                    onSuccess(response);
                    if (autoShowDialog) {
                        dialogStore.openSuccessDialog(
                            response.data.meta.message ||
                                "Data buku berhasil disimpan."
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error saving books:", error);
                    onChangeStatus("error");
                    onError(error);
                    errorCodes.push(error.response?.data?.meta?.code);
                    if (autoShowDialog) {
                        dialogStore.openErrorDialog(
                            error.response?.data?.meta?.message ||
                                "Terjadi kesalahan saat menyimpan data buku."
                        );
                    }
                });
        }

        if (
            errorCodes.length > 0 &&
            errorCodes.every((code) => code == "422")
        ) {
            onChangeStatus("success");
            if (autoShowDialog) {
                dialogStore.openSuccessDialog(
                    "Data buku berhasil disimpan dengan catatan beberapa data sudah ada di database."
                );
            }
        }
    }

    async function cleanBookTitles(
        bookIds: number[] | undefined,
        {
            autoShowDialog = false,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");

        await axios
            .post(
                "/api/admin/books/clean-titles",
                {
                    book_ids: bookIds.length > 0 ? bookIds : undefined,
                },
                {
                    headers: {
                        "Content-Type": "application/json",
                        Authorization: token,
                    },
                }
            )
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            "Data judul buku berhasil dibersihkan."
                    );
                }
            })
            .catch((error) => {
                console.error("Error cleaning book titles:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat membersihkan judul buku."
                    );
                }
            });
    }

    async function stemBookTitles(
        bookIds: number[] | undefined,
        {
            autoShowDialog = false,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");

        await axios
            .post(
                "/api/admin/books/stem-titles",
                {
                    book_ids: bookIds.length > 0 ? bookIds : undefined,
                },
                {
                    headers: {
                        "Content-Type": "application/json",
                        Authorization: token,
                    },
                }
            )
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            "Data judul buku berhasil distem."
                    );
                }
            })
            .catch((error) => {
                console.error("Error stemming book titles:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat stemming judul buku."
                    );
                }
            });
    }

    async function exportBooksExcel(
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
            .get("/admin/book-export", {
                headers: {
                    Authorization: token,
                },
                responseType: "blob",
            })
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        "Data buku berhasil diekspor."
                    );
                }

                // Create a link to download the file
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute("download", "books.xlsx");
                document.body.appendChild(link);
                link.click();
            })
            .catch((error) => {
                console.error("Error exporting books to excel:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat mengekspor data buku."
                    );
                }
            });
    }

    return {
        loadCategories,
        saveCategories,
        loadBooks,
        saveBooks,
        cleanBookTitles,
        stemBookTitles,
        exportBooksExcel,
    };
}
