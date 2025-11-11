import { defineStore } from "pinia";
import useBookService from "@/services/book-service";
import { computed, ref, watch } from "vue";

export const useScrapingStore = defineStore("scraping", () => {
    const categories = ref<CategoryModel[]>(
        JSON.parse(localStorage.getItem("scraping_categories")) || []
    );
    const getCategoriesStatus = ref<string>(
        JSON.parse(localStorage.getItem("scraping_getCategoriesStatus")) || ""
    );
    const saveCategoriesStatus = ref<string>(
        JSON.parse(localStorage.getItem("scraping_saveCategoriesStatus")) || ""
    );

    const selectedCategories = ref<CategoryModel[]>(
        JSON.parse(localStorage.getItem("scraping_selectedCategories")) || []
    );

    const books = ref<BookEntity[]>(
        JSON.parse(localStorage.getItem("scraping_books")) || []
    );
    const loadBooksStatus = ref<string>(
        JSON.parse(localStorage.getItem("scraping_loadBooksStatus")) || ""
    );

    const bookService = useBookService();

    const loadBookCurrentCategory = ref<CategoryModel | null>(
        JSON.parse(localStorage.getItem("scraping_loadBookCurrentCategory")) ||
            null
    );
    const loadBookCurrentPage = ref(
        JSON.parse(localStorage.getItem("scraping_loadBookCurrentPage")) || 0
    );
    const loadBookPage = ref(
        JSON.parse(localStorage.getItem("scraping_loadBookPage")) || 1
    );
    const loadBookLimit = 28;

    const totalBooksLoaded = computed(() => {
        return selectedCategories.value.reduce(
            (total, category) => total + (category.count_loaded_books || 0),
            0
        );
    });

    async function loadBooksByCategory(category: CategoryModel) {
        console.log(
            `Loading books for ${category.slug} - Page ${loadBookPage.value}`
        );

        if (
            loadBookCurrentCategory.value!.count_loaded_books! >=
            loadBookCurrentCategory.value!.total_data_books!
        ) {
            loadBooksStatus.value = "success";
            return;
        }

        // Load books page by page with delay 3 - 5 seconds
        loadBooksStatus.value = "loading";
        await bookService.loadBooks(
            {
                page: loadBookPage.value,
                limit: loadBookLimit,
                category: category,
            },
            {
                onSuccess: (response, totalData, isFromCache) => {
                    // Book just to preview per page
                    books.value = response;

                    // Update selected category
                    loadBookCurrentCategory.value = {
                        ...loadBookCurrentCategory.value,
                        count_loaded_books:
                            loadBookCurrentCategory.value.count_loaded_books <
                            totalData
                                ? (loadBookCurrentCategory.value
                                      ?.count_loaded_books || 0) +
                                  response.length
                                : totalData,
                        total_data_books: totalData,
                    };

                    selectedCategories.value = selectedCategories.value.map(
                        (cat) => {
                            if (
                                cat.slug === loadBookCurrentCategory.value.slug
                            ) {
                                return loadBookCurrentCategory.value;
                            }
                            return cat;
                        }
                    );

                    if (
                        loadBookCurrentCategory.value!.count_loaded_books! <
                        loadBookCurrentCategory.value!.total_data_books!
                    ) {
                        loadBookCurrentPage.value = loadBookPage.value;
                        loadBookPage.value += 1;

                        if (!isFromCache) {
                            // Delay 3 - 5 seconds
                            const delay =
                                Math.floor(Math.random() * 2000) + 3000;
                            setTimeout(() => {
                                loadBooksByCategory(category);
                            }, delay);
                        } else {
                            setTimeout(() => {
                                loadBooksByCategory(category);
                            }, 200);
                        }
                    } else {
                        loadBooksStatus.value = "success";
                    }
                },
                onChangeStatus: (status) => {
                    // loadBooksStatus.value = status;
                },
            }
        );
    }

    async function loadBooks() {
        // For each selected category, load books
        if (selectedCategories.value.length === 0) {
            alert("Please select at least one category");
            return;
        }

        // Reset
        for (const category of selectedCategories.value) {
            loadBookCurrentCategory.value = category;
            books.value = [];
            loadBookCurrentPage.value = 0;
            loadBookPage.value = 1;

            await loadBooksByCategory(category);
            loadBooksStatus.value = "loading";
            await new Promise((resolve) => setTimeout(resolve, 200)); // Wait for 200ms
        }

        loadBooksStatus.value = "success";
    }

    const saveBooksStatus = ref<string>("");

    function saveBooksByCategory(category: CategoryModel) {
        saveBooksStatus.value = "loading";
        bookService.saveBooks(category, {
            onSuccess: (response) => {
                saveBooksStatus.value = "success";
            },
        });
    }

    function saveBooks() {
        // For each selected category, save books
        if (selectedCategories.value.length === 0) {
            alert("Please select at least one category");
            return;
        }

        for (const category of selectedCategories.value) {
            if (
                category.count_loaded_books &&
                category.count_loaded_books > 0
            ) {
                saveBooksByCategory(category);
            }
        }
    }

    watch(categories, (newValue) => {
        localStorage.setItem("scraping_categories", JSON.stringify(newValue));
    });

    watch(getCategoriesStatus, (newValue) => {
        localStorage.setItem(
            "scraping_getCategoriesStatus",
            JSON.stringify(newValue)
        );
    });

    watch(saveCategoriesStatus, (newValue) => {
        localStorage.setItem(
            "scraping_saveCategoriesStatus",
            JSON.stringify(newValue)
        );
    });

    watch(selectedCategories, (newValue) => {
        localStorage.setItem(
            "scraping_selectedCategories",
            JSON.stringify(newValue)
        );
    });

    watch(books, (newValue) => {
        localStorage.setItem("scraping_books", JSON.stringify(newValue));
    });

    watch(loadBooksStatus, (newValue) => {
        localStorage.setItem(
            "scraping_loadBooksStatus",
            JSON.stringify(newValue)
        );
    });

    watch(loadBookCurrentCategory, (newValue) => {
        localStorage.setItem(
            "scraping_loadBookCurrentCategory",
            JSON.stringify(newValue)
        );
    });

    watch(loadBookCurrentPage, (newValue) => {
        localStorage.setItem(
            "scraping_loadBookCurrentPage",
            JSON.stringify(newValue)
        );
    });

    watch(loadBookPage, (newValue) => {
        localStorage.setItem("scraping_loadBookPage", JSON.stringify(newValue));
    });

    function clearStore() {
        categories.value = [];
        getCategoriesStatus.value = "";
        saveCategoriesStatus.value = "";
        selectedCategories.value = [];
        books.value = [];
        loadBooksStatus.value = "";
        loadBookCurrentCategory.value = null;
        loadBookCurrentPage.value = 0;
        loadBookPage.value = 1;
    }

    return {
        categories,
        getCategoriesStatus,
        saveCategoriesStatus,
        selectedCategories,
        books,
        loadBooksStatus,
        loadBookCurrentCategory,
        loadBookCurrentPage,
        loadBookPage,
        loadBooks,
        loadBooksByCategory,
        totalBooksLoaded,
        saveBooks,
        saveBooksStatus,
        bookService,
        clearStore,
    };
});
