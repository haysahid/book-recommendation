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
import RatingSmall from "@/Components/RatingSmall.vue";

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

const booksLimit = 10;

async function loadMore() {
    router.get(
        "/book",
        {
            page: booksPagination.value.current_page + 1,
            search: searchQuery.value,
            limit: booksLimit,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page: CustomPageProps) => {
                booksPagination.value = page.props.books;
                books.value = [...books.value, ...page.props.books.data];
                getQueryParam();

                // Scroll to newly loaded books
                setTimeout(() => {
                    const firstNewBook = document.querySelector(
                        `#booksSection a:nth-child(${
                            books.value.length -
                            page.props.books.data.length +
                            1
                        })`
                    ) as HTMLElement;
                    if (firstNewBook) {
                        firstNewBook.scrollIntoView({
                            behavior: "smooth",
                            block: "center",
                        });
                    }
                }, 300);
            },
        }
    );
}

const userRecommendedResult = ref<UserRecommendBookModel>(null);
const getRecommendedBooksStatus = ref(null);
const recommendedBooksLimit = 4;
async function getRecommendedBooks() {
    getRecommendedBooksStatus.value = "loading";

    await new Promise((resolve) => setTimeout(resolve, 600));

    await axios
        .get(`/api/recommended-books`, {
            params: {
                limit: recommendedBooksLimit,
            },
            headers: {
                Authorization: `Bearer ${cookieManager.getItem(
                    "access_token"
                )}`,
            },
        })
        .then((response) => {
            console.log(response.data.result);
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
                class="w-full mb-12 bg-primary-light!"
            >
                <div class="flex flex-col gap-4">
                    <div
                        class="flex gap-3 items-center justify-between"
                        :class="{
                            'justify-center animate-pulse':
                                getRecommendedBooksStatus == 'loading',
                        }"
                    >
                        <h2
                            class="flex items-center gap-2 font-semibold text-lg text-white"
                        >
                            <span>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <g clip-path="url(#clip0_483_27)">
                                        <path
                                            d="M14.4001 3.41911C14.4451 3.29619 14.5268 3.19008 14.6341 3.1151C14.7415 3.04013 14.8692 2.99993 15.0001 2.99993C15.131 2.99993 15.2588 3.04013 15.3661 3.1151C15.4734 3.19008 15.5551 3.29619 15.6001 3.41911L16.2101 5.08711C16.6895 6.39482 17.4481 7.58241 18.4329 8.56728C19.4178 9.55216 20.6054 10.3107 21.9131 10.7901L23.5811 11.4001C23.704 11.4451 23.8101 11.5268 23.8851 11.6341C23.9601 11.7415 24.0003 11.8692 24.0003 12.0001C24.0003 12.131 23.9601 12.2588 23.8851 12.3661C23.8101 12.4734 23.704 12.5551 23.5811 12.6001L21.9131 13.2101C20.6054 13.6895 19.4178 14.4481 18.4329 15.4329C17.4481 16.4178 16.6895 17.6054 16.2101 18.9131L15.6001 20.5811C15.5551 20.704 15.4734 20.8101 15.3661 20.8851C15.2588 20.9601 15.131 21.0003 15.0001 21.0003C14.8692 21.0003 14.7415 20.9601 14.6341 20.8851C14.5268 20.8101 14.4451 20.704 14.4001 20.5811L13.7901 18.9131C13.3107 17.6054 12.5522 16.4178 11.5673 15.4329C10.5824 14.4481 9.39482 13.6895 8.08711 13.2101L6.41911 12.6001C6.29619 12.5551 6.19008 12.4734 6.1151 12.3661C6.04013 12.2588 5.99993 12.131 5.99993 12.0001C5.99993 11.8692 6.04013 11.7415 6.1151 11.6341C6.19008 11.5268 6.29619 11.4451 6.41911 11.4001L8.08711 10.7901C9.39482 10.3107 10.5824 9.55216 11.5673 8.56728C12.5522 7.58241 13.3107 6.39482 13.7901 5.08711L14.4001 3.41911ZM8.00011 16.6751C8.01876 16.6238 8.05276 16.5794 8.09749 16.5481C8.14221 16.5168 8.1955 16.5 8.25011 16.5C8.30472 16.5 8.358 16.5168 8.40273 16.5481C8.44746 16.5794 8.48146 16.6238 8.50011 16.6751L8.75411 17.3691C8.954 17.914 9.27006 18.4088 9.68035 18.8193C10.0906 19.2297 10.5853 19.546 11.1301 19.7461L11.8251 20.0001C11.8764 20.0188 11.9208 20.0528 11.9521 20.0975C11.9834 20.1422 12.0002 20.1955 12.0002 20.2501C12.0002 20.3047 11.9834 20.358 11.9521 20.4027C11.9208 20.4475 11.8764 20.4815 11.8251 20.5001L11.1301 20.7541C10.5853 20.9542 10.0906 21.2705 9.68035 21.681C9.27006 22.0914 8.954 22.5863 8.75411 23.1311L8.50011 23.8251C8.48146 23.8764 8.44746 23.9208 8.40273 23.9521C8.358 23.9834 8.30472 24.0002 8.25011 24.0002C8.1955 24.0002 8.14221 23.9834 8.09749 23.9521C8.05276 23.9208 8.01876 23.8764 8.00011 23.8251L7.74611 23.1311C7.54622 22.5863 7.23016 22.0914 6.81987 21.681C6.40958 21.2705 5.91488 20.9542 5.37011 20.7541L4.67511 20.5001C4.62379 20.4815 4.57945 20.4475 4.54811 20.4027C4.51678 20.358 4.49997 20.3047 4.49997 20.2501C4.49997 20.1955 4.51678 20.1422 4.54811 20.0975C4.57945 20.0528 4.62379 20.0188 4.67511 20.0001L5.37011 19.7461C5.91488 19.546 6.40958 19.2297 6.81987 18.8193C7.23016 18.4088 7.54622 17.914 7.74611 17.3691L8.00011 16.6751ZM4.20011 0.21011C4.22284 0.148869 4.26377 0.096052 4.3174 0.0587543C4.37103 0.0214566 4.43479 0.00146484 4.50011 0.00146484C4.56543 0.00146484 4.62919 0.0214566 4.68282 0.0587543C4.73645 0.096052 4.77738 0.148869 4.80011 0.21011L5.10511 1.04311C5.34511 1.69694 5.72453 2.29071 6.21702 2.7832C6.70951 3.27569 7.30328 3.65511 7.95711 3.89511L8.79011 4.20011C8.85135 4.22284 8.90417 4.26377 8.94147 4.3174C8.97876 4.37103 8.99875 4.43479 8.99875 4.50011C8.99875 4.56543 8.97876 4.62919 8.94147 4.68282C8.90417 4.73645 8.85135 4.77738 8.79011 4.80011L7.95711 5.10511C7.30328 5.34511 6.70951 5.72453 6.21702 6.21702C5.72453 6.70951 5.34511 7.30328 5.10511 7.95711L4.80011 8.79011C4.77738 8.85135 4.73645 8.90417 4.68282 8.94147C4.62919 8.97876 4.56543 8.99875 4.50011 8.99875C4.43479 8.99875 4.37103 8.97876 4.3174 8.94147C4.26377 8.90417 4.22284 8.85135 4.20011 8.79011L3.89511 7.95711C3.65511 7.30328 3.27569 6.70951 2.7832 6.21702C2.29071 5.72453 1.69694 5.34511 1.04311 5.10511L0.21011 4.80011C0.148869 4.77738 0.096052 4.73645 0.0587543 4.68282C0.0214566 4.62919 0.00146484 4.56543 0.00146484 4.50011C0.00146484 4.43479 0.0214566 4.37103 0.0587543 4.3174C0.096052 4.26377 0.148869 4.22284 0.21011 4.20011L1.04311 3.89511C1.69694 3.65511 2.29071 3.27569 2.7832 2.7832C3.27569 2.29071 3.65511 1.69694 3.89511 1.04311L4.20011 0.21011Z"
                                            fill="currentColor"
                                        />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_483_27">
                                            <rect
                                                width="24"
                                                height="24"
                                                fill="white"
                                            />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </span>
                            <span>
                                {{
                                    getRecommendedBooksStatus === "loading"
                                        ? "Loading books for you..."
                                        : "Recommended Books"
                                }}
                            </span>
                        </h2>
                        <template v-if="userRecommendedResult?.strategy">
                            <Transition name="fade" appear>
                                <StatusChip
                                    v-if="userRecommendedResult?.strategy"
                                    :label="userRecommendedResult?.strategy"
                                    class="whitespace-nowrap"
                                    :class="{
                                        'text-blue-500! bg-blue-100!':
                                            userRecommendedResult?.strategy ===
                                            'Cold Start',
                                        'text-purple-500! bg-purple-100!':
                                            userRecommendedResult?.strategy ===
                                            'SVD Matrix Factorization',
                                    }"
                                />
                            </Transition>
                        </template>
                        <!-- <ThreeDotsLoading v-else class="text-white mx-4" /> -->
                    </div>
                    <div
                        v-if="userRecommendedResult?.results?.length"
                        class="w-full"
                    >
                        <Transition name="accordion" mode="out-in" appear>
                            <div
                                class="grid gap-3 sm:gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                            >
                                <a
                                    v-for="(
                                        book, index
                                    ) in userRecommendedResult.results"
                                    :key="book.id"
                                    :href="`/book/${book.slug}`"
                                >
                                    <Transition
                                        name="fade"
                                        mode="out-in"
                                        appear
                                        @before-enter="
                                    (el: HTMLElement) => {
                                        el.style.transitionDelay =
                                            index * 100 + 'ms';
                                    }
                                "
                                        @after-enter="
                                    (el: HTMLElement) => {
                                        el.style.transitionDelay = '';
                                    }
                                "
                                        @after-leave="
                                    (el: HTMLElement) => {
                                        el.style.transitionDelay = '';
                                    }
                                "
                                    >
                                        <BookCard
                                            :index="index"
                                            :book="book"
                                            class="h-full"
                                        />
                                    </Transition>
                                </a>
                            </div>
                        </Transition>

                        <!-- <div v-else class="flex items-center justify-center">
                        <div
                            v-if="getRecommendedBooksStatus === 'loading'"
                            class="grid gap-3 sm:gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 w-full"
                        >
                            <div
                                v-for="n in recommendedBooksLimit"
                                :key="n"
                                class="aspect-3/5 w-full bg-white/50 rounded-lg animate-pulse"
                            ></div>
                        </div>
                        <div
                            v-else
                            class="h-[20vh] flex items-center justify-center mb-6"
                        >
                            <p class="text-sm text-center text-white">
                                No data found.
                            </p>
                        </div>
                    </div> -->
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
                    id="booksSection"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
                >
                    <template v-for="(book, index) in books" :key="book.id">
                        <Transition
                            name="fade"
                            mode="out-in"
                            appear
                            @before-enter="
                                    (el: HTMLElement) => {
                                        el.style.transitionDelay =
                                            index % booksLimit * 50 + 'ms';
                                    }
                                "
                            @after-enter="
                                    (el: HTMLElement) => {
                                        el.style.transitionDelay = '';
                                    }
                                "
                            @after-leave="
                                    (el: HTMLElement) => {
                                        el.style.transitionDelay = '';
                                    }
                                "
                        >
                            <a
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
                                        :src="$getImageUrl(book.image)"
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
                                        <span
                                            class="text-sm text-muted-foreground"
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
                                            class="flex items-center gap-2 justify-between"
                                        >
                                            <p
                                                class="text-xl font-semibold text-gray-900"
                                            >
                                                {{
                                                    $formatCurrency(
                                                        book.final_price
                                                    )
                                                }}
                                            </p>
                                            <RatingSmall
                                                :rating="book.average_rating"
                                                :ratingCount="book.rating_count"
                                            />
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
                                                {{
                                                    $formatCurrency(
                                                        book.slice_price
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>

                                    <div
                                        class="flex items-center justify-between pt-2 border-t border-border/50"
                                    >
                                        <span
                                            class="text-xs text-muted-foreground"
                                        >
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
                        </Transition>
                    </template>
                </div>
            </div>

            <!-- Load more -->
            <div class="flex justify-center mt-6">
                <button
                    v-if="books.length < booksPagination.total"
                    @click="loadMore()"
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
