<script setup lang="ts">
import LandingMainContainer from "@/Components/LandingMainContainer.vue";
import LandingLayout from "@/Layouts/LandingLayout.vue";
import useBookService from "@/services/book-service";
import { computed, onMounted, ref } from "vue";
import BookCard from "../Admin/Book/BookCard.vue";
import ThreeDotsLoading from "@/Components/ThreeDotsLoading.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Tooltip from "@/Components/Tooltip.vue";
import { useCartStore } from "@/stores/cart-store";
import { useFavoriteStore } from "@/stores/favorite-store";
import Rating from "@/Components/Rating.vue";
import RatingSmall from "@/Components/RatingSmall.vue";
import ReviewCard from "@/Components/ReviewCard.vue";
import RatingSummary from "@/Components/RatingSummary.vue";
import DefaultCard from "@/Components/DefaultCard.vue";

const props = defineProps({
    book: {
        type: Object as () => BookEntity,
        required: true,
    },
    reviews: {
        type: Array as () => BookReviewEntity[],
        required: false,
        default: () => [],
    },
    relatedBooks: {
        type: Array as () => BookEntity[],
        required: false,
        default: () => [],
    },
});

const bookDetail = ref<BookDetailModel | null>(null);
const loadBookDetailStatus = ref(null);

const cartStore = useCartStore();
const favoriteStore = useFavoriteStore();

const isFavorite = computed(() => {
    return favoriteStore.isBookInFavorite(props.book.id);
});

onMounted(async () => {
    useBookService().loadBookDetail(props.book, {
        onSuccess: (data) => {
            bookDetail.value = data;
        },
        onChangeStatus: (status) => {
            loadBookDetailStatus.value = status;
        },
    });
});
</script>

<template>
    <LandingLayout :title="props.book.title">
        <LandingMainContainer class="py-6 sm:py-12">
            <!-- Book -->
            <div class="grid gap-4 md:gap-8 lg:gap-12 md:grid-cols-2">
                <!-- Image -->
                <div class="">
                    <div
                        class="h-96 sm:h-[540px] w-full transition-all duration-300 ease-in-out flex items-center justify-center"
                    >
                        <img
                            v-if="props.book.image"
                            :src="props.book.image"
                            :alt="`Cover image of ${book.title}`"
                            class="w-full h-full object-contain rounded-lg hover:scale-105 transition-transform duration-300 ease-in-out shrink-0"
                        />
                        <div
                            v-else
                            class="flex flex-col items-center justify-center gap-3 bg-muted h-full rounded-lg aspect-3/4"
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
                </div>

                <!-- Details -->
                <div class="py-6">
                    <h1 class="text-3xl font-bold">{{ props.book.title }}</h1>

                    <div class="flex flex-wrap gap-x-2 mt-1 items-center">
                        <p class="text-lg text-muted-foreground">
                            by {{ props.book.author }}
                        </p>
                        <span class="text-lg text-gray-400">•</span>
                        <Rating
                            :rating="props.book.average_rating"
                            :ratingCount="props.book.rating_count"
                        />
                    </div>

                    <!-- Price -->
                    <div class="mt-4 flex flex-col gap-2">
                        <div class="text-2xl font-semibold text-gray-900">
                            {{ $formatCurrency(props.book.final_price) }}
                        </div>
                        <div
                            v-if="props.book.discount"
                            class="flex gap-2 items-center"
                        >
                            <div
                                class="text-sm font-medium text-red-600 bg-red-100 px-2 py-1 rounded-md"
                            >
                                {{ props.book.discount }}%
                            </div>
                            <span
                                class="text-base font-semibold text-gray-400 line-through"
                            >
                                {{ $formatCurrency(props.book.slice_price) }}
                            </span>
                        </div>
                    </div>

                    <!-- Specifications -->
                    <div v-if="bookDetail" class="mt-6 grid gap-4 grid-cols-2">
                        <div v-for="spec in bookDetail.specifications" class="">
                            <div class="font-medium text-gray-500 text-sm">
                                {{ spec.label }}
                            </div>
                            <div>{{ spec.value || "-" }}</div>
                        </div>
                    </div>
                    <div
                        v-else-if="loadBookDetailStatus === 'loading'"
                        class="my-10 text-primary flex items-center gap-3 justify-start"
                    >
                        <ThreeDotsLoading />
                    </div>

                    <!-- CTA Buttons -->
                    <div class="flex items-center gap-3 mt-6">
                        <SecondaryButton
                            v-if="cartStore.isBookInCart(props.book.id)"
                            class="border-red-600 text-red-600 hover:bg-red-600/10"
                            @click="
                                if (cartStore.isBookInCart(props.book.id)) {
                                    const group = cartStore.groups[0];

                                    console.log('group length: ', group.items);

                                    if (group.items.length == 1) {
                                        cartStore.removeGroup(group);
                                    } else {
                                        cartStore.updateGroup({
                                            ...group,
                                            items: group.items.filter(
                                                (item) =>
                                                    item.book_id !=
                                                    props.book.id
                                            ),
                                        });
                                    }
                                }
                            "
                        >
                            <template #prefix>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="25"
                                    viewBox="0 0 24 25"
                                    class="fill-red-600"
                                >
                                    <path
                                        d="M12 0L8 4H10V8H14V4H16M1 2V4H3L6.6 11.6L5.2 14C5.1 14.3 5 14.6 5 15C5 16.1 5.9 17 7 17H19V15H7.4C7.3 15 7.2 14.9 7.2 14.8V14.7L8.1 13H15.5C16.2 13 16.9 12.6 17.2 12L21.1 5L19.4 4L15.5 11H8.5L4.3 2M7 18C5.9 18 5 18.9 5 20C5 21.1 5.9 22 7 22C8.1 22 9 21.1 9 20C9 18.9 8.1 18 7 18ZM17 18C15.9 18 15 18.9 15 20C15 21.1 15.9 22 17 22C18.1 22 19 21.1 19 20C19 18.9 18.1 18 17 18Z"
                                    />
                                </svg>
                            </template>
                            Remove from Cart
                        </SecondaryButton>
                        <PrimaryButton
                            v-else
                            @click="
                                if (cartStore.groups.length > 0) {
                                    const group = cartStore.groups[0];

                                    cartStore.updateGroup({
                                        ...group,
                                        items: [
                                            ...group.items,
                                            {
                                                store_id: $page.props.store.id,
                                                store: $page.props.store,
                                                book_id: props.book.id,
                                                book: props.book,
                                                image: props.book.image,
                                                quantity: 1,
                                                selected: true,
                                                created_at:
                                                    new Date().toISOString(),
                                            } as CartItemModel,
                                        ],
                                    });
                                } else {
                                    cartStore.addGroup({
                                        store_id: $page.props.store.id,
                                        store: $page.props.store,
                                        created_at: new Date().toISOString(),
                                        items: [
                                            {
                                                store_id: $page.props.store.id,
                                                store: $page.props.store,
                                                book_id: props.book.id,
                                                book: props.book,
                                                image: props.book.image,
                                                quantity: 1,
                                                selected: true,
                                                created_at:
                                                    new Date().toISOString(),
                                            } as CartItemModel,
                                        ],
                                    } as CartGroupModel);
                                }
                            "
                        >
                            <template #prefix>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="25"
                                    viewBox="0 0 24 25"
                                    class="fill-white"
                                >
                                    <path
                                        d="M10 0V4H8L12 8L16 4H14V0M1 2V4H3L6.6 11.6L5.2 14C5.1 14.3 5 14.6 5 15C5 16.1 5.9 17 7 17H19V15H7.4C7.3 15 7.2 14.9 7.2 14.8V14.7L8.1 13H15.5C16.2 13 16.9 12.6 17.2 12L21.1 5L19.4 4L15.5 11H8.5L4.3 2M7 18C5.9 18 5 18.9 5 20C5 21.1 5.9 22 7 22C8.1 22 9 21.1 9 20C9 18.9 8.1 18 7 18ZM17 18C15.9 18 15 18.9 15 20C15 21.1 15.9 22 17 22C18.1 22 19 21.1 19 20C19 18.9 18.1 18 17 18Z"
                                    />
                                </svg>
                            </template>
                            Add to Cart
                        </PrimaryButton>

                        <!-- <a
                            :href="`https://www.gramedia.com/products/${props.book.slug}`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-block w-full md:w-auto"
                        >
                            <SecondaryButton> Buy on Gramedia </SecondaryButton>
                        </a> -->

                        <Tooltip
                            id="book-favorite-button"
                            placement="bottom"
                            :backgroundColor="
                                isFavorite ? 'bg-gray-500' : 'bg-pink-500'
                            "
                            :textColor="
                                isFavorite ? 'text-white' : 'text-white'
                            "
                        >
                            <template #content>
                                {{
                                    isFavorite
                                        ? "Remove from Favorites"
                                        : "Add to Favorites"
                                }}
                            </template>

                            <SecondaryButton
                                @click="
                                    if (isFavorite) {
                                        favoriteStore.removeBookFromFavorite(
                                            props.book.id
                                        );
                                    } else {
                                        favoriteStore.addBookToFavorite(
                                            props.book
                                        );
                                    }
                                "
                                class="size-[43px] p-0!"
                                :class="{
                                    'border-pink-500 focus:border-pink-500 hover:bg-pink-500/10 focus:ring-pink-500/30':
                                        isFavorite,
                                }"
                            >
                                <svg
                                    v-if="isFavorite"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    class="size-5 text-pink-500"
                                >
                                    <path
                                        d="M12 20.3249C11.7667 20.3249 11.5293 20.2832 11.288 20.1999C11.0467 20.1166 10.834 19.9832 10.65 19.7999L8.925 18.2249C7.15833 16.6082 5.56233 15.0042 4.137 13.4129C2.71167 11.8216 1.99933 10.0672 2 8.1499C2 6.58324 2.525 5.2749 3.575 4.2249C4.625 3.1749 5.93333 2.6499 7.5 2.6499C8.38333 2.6499 9.21667 2.83724 10 3.2119C10.7833 3.58657 11.45 4.09924 12 4.7499C12.55 4.0999 13.2167 3.58757 14 3.2129C14.7833 2.83824 15.6167 2.65057 16.5 2.6499C18.0667 2.6499 19.375 3.1749 20.425 4.2249C21.475 5.2749 22 6.58324 22 8.1499C22 10.0666 21.2917 11.8249 19.875 13.4249C18.4583 15.0249 16.85 16.6332 15.05 18.2499L13.35 19.7999C13.1667 19.9832 12.9543 20.1166 12.713 20.1999C12.4717 20.2832 12.234 20.3249 12 20.3249Z"
                                        fill="currentColor"
                                    />
                                </svg>
                                <svg
                                    v-else
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    class="size-5"
                                >
                                    <path
                                        d="M12 20.3249C11.7667 20.3249 11.5293 20.2832 11.288 20.1999C11.0467 20.1166 10.834 19.9832 10.65 19.7999L8.925 18.2249C7.15833 16.6082 5.56233 15.0042 4.137 13.4129C2.71167 11.8216 1.99933 10.0672 2 8.1499C2 6.58324 2.525 5.2749 3.575 4.2249C4.625 3.1749 5.93333 2.6499 7.5 2.6499C8.38333 2.6499 9.21667 2.83724 10 3.2119C10.7833 3.58657 11.45 4.09924 12 4.7499C12.55 4.0999 13.2167 3.58757 14 3.2129C14.7833 2.83824 15.6167 2.65057 16.5 2.6499C18.0667 2.6499 19.375 3.1749 20.425 4.2249C21.475 5.2749 22 6.58324 22 8.1499C22 10.0666 21.2917 11.8249 19.875 13.4249C18.4583 15.0249 16.85 16.6332 15.05 18.2499L13.35 19.7999C13.1667 19.9832 12.9543 20.1166 12.713 20.1999C12.4717 20.2832 12.234 20.3249 12 20.3249ZM11.05 6.7499C10.5667 6.06657 10.05 5.54557 9.5 5.1869C8.95 4.82824 8.28333 4.64924 7.5 4.6499C6.5 4.6499 5.66667 4.98324 5 5.6499C4.33333 6.31657 4 7.1499 4 8.1499C4 9.01657 4.30833 9.93757 4.925 10.9129C5.54167 11.8882 6.27933 12.8339 7.138 13.7499C7.99667 14.6659 8.88 15.5242 9.788 16.3249C10.696 17.1256 11.4333 17.7839 12 18.2999C12.5667 17.7832 13.3043 17.1249 14.213 16.3249C15.1217 15.5249 16.005 14.6666 16.863 13.7499C17.721 12.8332 18.4583 11.8876 19.075 10.9129C19.6917 9.93824 20 9.01724 20 8.1499C20 7.1499 19.6667 6.31657 19 5.6499C18.3333 4.98324 17.5 4.6499 16.5 4.6499C15.7167 4.6499 15.05 4.82924 14.5 5.1879C13.95 5.54657 13.4333 6.06724 12.95 6.7499C12.8333 6.91657 12.6917 7.04157 12.525 7.1249C12.3583 7.20824 12.1833 7.2499 12 7.2499C11.8167 7.2499 11.6417 7.20824 11.475 7.1249C11.3083 7.04157 11.1667 6.91657 11.05 6.7499Z"
                                        fill="currentColor"
                                    />
                                </svg>
                            </SecondaryButton>
                        </Tooltip>
                    </div>

                    <!-- Reviews -->
                    <div class="mt-8">
                        <h2 class="text-xl font-bold mb-2">Reviews</h2>

                        <DefaultCard>
                            <!-- Rating Summary -->
                            <RatingSummary
                                :book="props.book"
                                :reviews="props.reviews"
                            />

                            <!-- Divider -->
                            <hr
                                v-if="props.reviews.length > 0"
                                class="my-4 border-t border-gray-200"
                            />

                            <div
                                v-if="props.reviews.length > 0"
                                class="flex flex-col gap-4"
                            >
                                <template
                                    v-for="(review, index) in props.reviews"
                                    :key="review.id"
                                >
                                    <ReviewCard :review="review" />

                                    <!-- Divider -->
                                    <hr
                                        v-if="index < props.reviews.length - 1"
                                        class="border-t border-gray-200"
                                    />
                                </template>
                            </div>
                        </DefaultCard>
                    </div>
                </div>
            </div>

            <!-- Related Books -->
            <div v-if="props.relatedBooks.length" class="mt-2">
                <h2 class="text-2xl font-bold mb-6">Related Books</h2>
                <div
                    class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
                >
                    <a
                        v-for="(relatedBook, index) in props.relatedBooks"
                        :key="relatedBook.id"
                        :href="`/book/${relatedBook.slug}`"
                    >
                        <BookCard
                            :book="relatedBook"
                            :index="index"
                            class="h-full"
                        />
                    </a>
                </div>
            </div>
        </LandingMainContainer>
    </LandingLayout>
</template>
