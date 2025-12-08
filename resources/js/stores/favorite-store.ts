import { computed, ref } from "vue";
import { defineStore } from "pinia";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";
import CustomPageProps from "@/types/model/CustomPageProps";
import cookieManager from "@/plugins/cookie-manager";

export const useFavoriteStore = defineStore("favorite", () => {
    const token = "Bearer " + cookieManager.getItem("access_token");

    const page = usePage<CustomPageProps>();

    const isLoggedIn = computed(() => {
        return !!page.props.auth.user;
    });

    const key = "favorite";

    const books = ref(
        localStorage.getItem(key)
            ? (JSON.parse(localStorage.getItem(key)) as BookEntity[])
            : []
    );

    const isBookInFavorite = (bookId: number) => {
        return books.value.some((book) => book.id === bookId);
    };

    const addBookToFavorite = async (book: BookEntity) => {
        if (isBookInFavorite(book.id)) return;

        books.value.push(book);
        localStorage.setItem(key, JSON.stringify(books.value));

        if (!isLoggedIn.value) return;

        try {
            await axios.post(
                "/api/favorite",
                { book_id: book.id },
                {
                    headers: { Authorization: token },
                }
            );
        } catch (error) {
            console.error("Failed to sync favorite with server:", error);
        }
    };

    const removeBookFromFavorite = async (bookId: number) => {
        books.value = books.value.filter((book) => book.id !== bookId);
        localStorage.setItem(key, JSON.stringify(books.value));

        if (!isLoggedIn.value) return;

        try {
            await axios.delete(`/api/favorite/${bookId}`, {
                headers: { Authorization: token },
            });
        } catch (error) {
            console.error(
                "Failed to sync favorite removal with server:",
                error
            );
        }
    };

    const syncFavorite = async () => {
        try {
            const response = await axios.get("/api/favorite", {
                headers: {
                    Authorization: isLoggedIn.value ? token : undefined,
                },
            });
            books.value = response.data as BookEntity[];
            localStorage.setItem(key, JSON.stringify(books.value));
        } catch (error) {
            console.error("Failed to sync favorite from server:", error);
        }
    };

    const clearFavorite = () => {
        books.value = [];
        localStorage.removeItem(key);

        if (!isLoggedIn.value) return;

        try {
            axios.delete("/api/favorite/clear", {
                headers: { Authorization: token },
            });
        } catch (error) {
            console.error("Failed to clear favorite on server:", error);
        }
    };

    return {
        books,
        isBookInFavorite,
        addBookToFavorite,
        removeBookFromFavorite,
        syncFavorite,
        clearFavorite,
    };
});
