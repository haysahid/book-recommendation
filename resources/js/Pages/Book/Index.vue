<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { Head, router } from "@inertiajs/vue3";
import CustomPageProps from "@/types/model/CustomPageProps";
import LandingHeader from "@/Components/LandingHeader.vue";
import LandingMainContainer from "@/Components/LandingMainContainer.vue";
import { scrollToTop } from "@/plugins/helpers";
import ThreeDotsLoading from "@/Components/ThreeDotsLoading.vue";
import axios from "axios";
import cookieManager from "@/plugins/cookie-manager";
import StatusChip from "@/Components/StatusChip.vue";
import BookCard from "../Admin/Book/BookCard.vue";
import DefaultCard from "@/Components/DefaultCard.vue";

const props = defineProps({
    books: {
        type: Object as () => PaginationModel<BookEntity>,
        required: true,
    },
});

const searchQuery = ref(null as string | undefined);

const booksPagination = ref<PaginationModel<BookEntity>>(props.books);
const books = ref<BookEntity[]>(booksPagination.value.data);

const handleSearch = () => {
    router.get(
        "/book",
        { search: searchQuery.value },
        {
            preserveState: true,
            onSuccess: (page: CustomPageProps) => {
                books.value = page.props.books.data;
                getQueryParam();
            },
        }
    );
};

const currentSearch = ref(searchQuery.value);

const getQueryParam = () => {
    const urlParams = new URLSearchParams(window.location.search);
    searchQuery.value = urlParams.get("search") || undefined;
    if (!searchQuery.value) {
        removeUrlParam("search");
    }

    currentSearch.value = searchQuery.value;

    removeUrlParam("page");
};

const removeUrlParam = (param: string) => {
    const url = new URL(window.location.href);
    url.searchParams.delete(param);
    window.history.replaceState({}, document.title, url.toString());
};

const scrolled = ref(false);
const scrollThreshold = 540;

const handleScroll = () => {
    scrolled.value = window.scrollY > scrollThreshold;
};

const isScrolled = computed(() => {
    return scrolled.value;
});

const userRecommendedResult = ref<UserRecommendBookModel>(null);
const getRecommendedBooksStatus = ref(null);
function getRecommendedBooks() {
    getRecommendedBooksStatus.value = "loading";
    axios
        .get(`/api/recommended-books`, {
            params: {
                limit: 4,
            },
            headers: {
                Authorization: `Bearer ${cookieManager.getItem(
                    "access_token"
                )}`,
            },
        })
        .then((response) => {
            userRecommendedResult.value = response.data.result;
            getRecommendedBooksStatus.value = "success";
        })
        .catch((error) => {
            console.error("Error fetching vouchers:", error);
            getRecommendedBooksStatus.value = "error";
        });
}
getRecommendedBooks();

onMounted(() => {
    window.addEventListener("scroll", handleScroll);
    getQueryParam();
});
</script>

<template>
    <Head title="Search Books" />

    <div class="min-h-screen bg-white">
        <transition name="accordion">
            <LandingHeader
                v-if="isScrolled"
                :invertColor="true"
                class="fixed! top-0 left-0 right-0 z-50 border-b-0!"
            />
        </transition>

        <!-- Hero Section -->
        <div
            class="relative overflow-hidden bg-gradient-hero pt-2 pb-20 mb-12 animate-fade-in"
        >
            <LandingHeader
                :invertColor="true"
                class="border-b-0! mb-8 md:mb-6 bg-transparent! bg-none"
            />

            <div class="max-w-4xl mx-auto text-center px-6 sm:px-12">
                <div
                    class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full mb-6 text-white border border-white/30"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-book-open w-4 h-4"
                    >
                        <path d="M12 7v14"></path>
                        <path
                            d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"
                        ></path>
                    </svg>
                    <span class="text-sm font-medium"
                        >Discover Your Next Favorite Read</span
                    >
                </div>

                <h1
                    class="text-5xl md:text-6xl font-bold text-white mb-6 leading-tight"
                >
                    Find Books That
                    <br />
                    <span
                        class="bg-linear-to-r from-white to-white/80 bg-clip-text text-transparent"
                    >
                        Inspire You
                    </span>
                </h1>

                <p class="text-xl text-white/90 mb-8 max-w-2xl mx-auto">
                    Search through thousands of books and find the perfect story
                    for your journey
                </p>

                <!-- Search Bar -->
                <div class="max-w-2xl mx-auto">
                    <div class="relative group">
                        <div
                            class="absolute inset-0 bg-white/20 rounded-2xl blur-xl group-hover:bg-white/30 transition-all duration-300"
                        />
                        <div
                            class="relative flex gap-3 bg-white/95 backdrop-blur-md p-3 rounded-2xl shadow-glow"
                        >
                            <div class="relative flex-1">
                                <!-- Search icon -->
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="lucide lucide-search absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground"
                                >
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.3-4.3"></path>
                                </svg>
                                <input
                                    v-model="searchQuery"
                                    @keyup.enter="handleSearch"
                                    autofocus
                                    placeholder="Search for books..."
                                    class="w-full pl-12 h-12 border-0 bg-transparent text-foreground placeholder:text-muted-foreground focus:ring-2 focus:ring-primary rounded-lg outline-none"
                                />
                            </div>
                            <button
                                @click="handleSearch"
                                class="px-8 h-12 bg-primary hover:bg-primary/90 text-primary-foreground rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:scale-105 font-medium"
                            >
                                Search
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Decorative Elements -->
            <div
                class="absolute top-20 left-10 w-72 h-72 bg-accent/20 rounded-full blur-3xl pointer-events-none"
            />
            <div
                class="absolute bottom-20 right-10 w-96 h-96 bg-secondary/20 rounded-full blur-3xl pointer-events-none"
            />
        </div>

        <!-- Results Section -->
        <LandingMainContainer class="mb-20">
            <!-- Recommended Books -->
            <DefaultCard
                v-if="$page.props.auth.user && !currentSearch"
                class="w-full mb-8 bg-primary-light!"
            >
                <div class="flex gap-3 items-center mb-4">
                    <h2 class="font-semibold text-lg text-white">
                        Recommended Books
                    </h2>
                    <StatusChip
                        v-if="userRecommendedResult?.strategy"
                        :label="userRecommendedResult?.strategy"
                        :class="{
                            'text-blue-500! bg-blue-100!':
                                userRecommendedResult?.strategy ===
                                'Cold Start',
                            'text-purple-500! bg-purple-100!':
                                userRecommendedResult?.strategy ===
                                'SVD Matrix Factorization',
                        }"
                    />
                </div>
                <div class="w-full mt-2.5">
                    <div
                        v-if="userRecommendedResult?.results?.length"
                        class="grid gap-3 sm:gap-4 grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                    >
                        <a
                            v-for="(
                                book, index
                            ) in userRecommendedResult.results"
                            :key="book.id"
                            :href="`/book/${book.slug}`"
                        >
                            <BookCard
                                :index="index"
                                :book="book"
                                class="h-full"
                            />
                        </a>
                    </div>
                    <div
                        v-else
                        class="flex items-center justify-center h-10 mb-6"
                    >
                        <ThreeDotsLoading
                            v-if="getRecommendedBooksStatus === 'loading'"
                        />
                        <p v-else class="text-sm text-center text-gray-500">
                            No data found.
                        </p>
                    </div>
                </div>
            </DefaultCard>

            <!-- Books -->
            <div v-if="books.length > 0" class="mb-12">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-foreground">
                        {{
                            currentSearch
                                ? `Search Results for "${currentSearch}"`
                                : "Popular Books"
                        }}
                    </h2>
                    <span class="text-muted-foreground text-sm md:text-base">
                        {{ $formatNumber(booksPagination.total) }} results found
                    </span>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <a
                        v-for="(book, index) in books"
                        :key="book.id"
                        :href="`/book/${book.slug}`"
                        class="group relative overflow-hidden rounded-xl border-0 bg-gradient-card hover:shadow-lg transition-all duration-300 hover:-translate-y-2 animate-slide-up cursor-pointer p-6"
                        :style="{ animationDelay: `${index * 0.1}s` }"
                    >
                        <!-- Ranking Badge -->
                        <div
                            v-if="index < 3 && book.score"
                            class="absolute top-4 left-4 z-10 inline-flex items-center gap-1 bg-primary text-white border-0 rounded-full shadow-md px-3 py-1 text-sm"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="lucide lucide-star w-3 h-3 mr-1 fill-current"
                            >
                                <path
                                    d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"
                                ></path>
                            </svg>

                            #{{ index + 1 }}
                        </div>

                        <!-- Book Cover -->
                        <div
                            class="relative mb-4 overflow-hidden rounded-xl bg-muted aspect-3/4 flex items-center justify-center group-hover:scale-105 transition-transform duration-300"
                        >
                            <img
                                v-if="book.image"
                                :src="book.image"
                                :alt="book.title"
                                class="w-full h-full object-cover"
                            />
                            <div
                                v-else
                                class="flex flex-col items-center justify-center gap-3"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="lucide lucide-book-open w-12 h-12 text-muted-foreground/50"
                                >
                                    <path d="M12 7v14"></path>
                                    <path
                                        d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"
                                    ></path>
                                </svg>
                                <span class="text-sm text-muted-foreground"
                                    >No Cover</span
                                >
                            </div>
                        </div>

                        <!-- Book Info -->
                        <div class="space-y-2">
                            <h3
                                class="text-xl font-bold text-foreground line-clamp-2 group-hover:text-primary transition-colors"
                            >
                                {{ book.title }}
                            </h3>
                            <p class="text-sm text-muted-foreground">
                                by {{ book.author }}
                            </p>

                            <!-- Price -->
                            <div class="flex flex-col gap-2">
                                <div
                                    class="text-xl font-semibold text-gray-900"
                                >
                                    {{ $formatCurrency(book.final_price) }}
                                </div>
                                <div
                                    v-if="book.discount"
                                    class="flex gap-2 items-center"
                                >
                                    <div
                                        class="text-sm font-medium text-red-600 bg-red-100 px-2 py-1 rounded-md"
                                    >
                                        {{ book.discount }}%
                                    </div>
                                    <span
                                        class="text-base font-semibold text-gray-400 line-through"
                                    >
                                        {{ $formatCurrency(book.slice_price) }}
                                    </span>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-between pt-2 border-t border-border/50"
                            >
                                <span class="text-xs text-muted-foreground">
                                    {{ book.store_name }}
                                </span>
                                <span
                                    class="text-xs font-mono text-muted-foreground"
                                >
                                    {{ book.isbn }}
                                </span>
                            </div>
                        </div>

                        <!-- Recommendation Score Badge -->
                        <div
                            v-if="book.score"
                            class="absolute top-4 right-4 inline-flex items-center gap-1 bg-white/90 text-foreground border border-border rounded-full shadow-md px-3 py-1 text-sm"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="lucide lucide-thumbs-up w-4 h-4 text-primary"
                            >
                                <path
                                    d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-7A2 2 0 0 0 19.66 7H14z"
                                ></path>
                                <path
                                    d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"
                                ></path>
                            </svg>
                            {{ book.score.toFixed(2) }}
                        </div>

                        <!-- Hover Glow Effect -->
                        <div
                            class="absolute inset-0 bg-linear-to-t from-primary/10 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"
                        />
                    </a>
                </div>
            </div>

            <!-- Load more -->
            <div class="flex justify-center mt-6">
                <button
                    v-if="books.length < booksPagination.total"
                    @click="
                        router.get(
                            '/book',
                            {
                                page: booksPagination.current_page + 1,
                                search: searchQuery,
                            },
                            {
                                preserveState: true,
                                preserveScroll: true,
                                onSuccess: (page: CustomPageProps) => {
                                    booksPagination = page.props.books;
                                    books = [
                                        ...books,
                                        ...page.props.books.data,
                                    ];
                                    getQueryParam();
                                },
                            }
                        )
                    "
                    class="px-6 py-2 bg-primary hover:bg-primary/90 text-primary-foreground rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:scale-105 font-medium"
                >
                    Load More
                </button>
                <button
                    v-else
                    disabled
                    class="px-6 py-2 bg-muted text-muted-foreground rounded-lg font-medium cursor-not-allowed"
                >
                    No More Books
                </button>
            </div>
        </LandingMainContainer>

        <!-- Scroll to Top -->
        <transition name="accordion">
            <button
                v-if="scrolled"
                type="button"
                @click="scrollToTop()"
                class="fixed bottom-6 right-6 p-3 bg-primary text-white rounded-full shadow-lg hover:bg-primary-dark transition-colors duration-300 ease-in-out"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M5 15l7-7 7 7"
                    />
                </svg>
            </button>
        </transition>
    </div>
</template>
