<script setup lang="ts">
import LandingSection from "@/Components/LandingSection.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import LandingLayout from "@/Layouts/LandingLayout.vue";
import { Link } from "@inertiajs/vue3";
import { useFavoriteStore } from "@/stores/favorite-store";
import BookCard from "../Admin/Book/BookCard.vue";

const favoriteStore = useFavoriteStore();
favoriteStore.syncFavorite();
</script>

<template>
    <LandingLayout :title="`Favorite (${favoriteStore.books.length})`">
        <div
            class="p-6 sm:p-12 md:px-[100px] md:py-[60px] flex flex-col gap-2 sm:gap-3"
            :class="{
                'min-h-[60vh] items-center justify-center gap-4':
                    favoriteStore.books.length == 0,
            }"
        >
            <h1 class="text-xl font-bold text-start sm:text-center">
                {{
                    favoriteStore.books.length > 0
                        ? `Favorite (${favoriteStore.books.length} item)`
                        : "Favorite is Empty"
                }}
            </h1>
            <div
                v-if="favoriteStore.books.length == 0"
                class="flex flex-col items-center gap-y-6"
            >
                <p class="text-sm text-center text-gray-700 sm:text-base">
                    You haven't added any products to the cart.
                </p>
                <Link href="/book">
                    <PrimaryButton class="py-2.5 px-5 mx-auto">
                        Search Books
                    </PrimaryButton>
                </Link>
            </div>
            <p v-else class="text-sm text-gray-700 text-start sm:text-center">
                These are your favorite books.
            </p>
        </div>

        <div
            data-aos="fade-up"
            data-aos-duration="600"
            class="p-6 pt-0! sm:p-12 md:p-[100px] flex flex-col gap-4 sm:gap-12"
        >
            <LandingSection
                v-if="favoriteStore.books.length > 0"
                class="items-start! justify-start!"
            >
                <div
                    class="flex flex-col items-center justify-center w-full gap-5 mx-auto lg:flex-row lg:items-start sm:gap-8 max-w-7xl"
                >
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
                    >
                        <a
                            v-for="(book, index) in favoriteStore.books"
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
                </div>
            </LandingSection>
        </div>
    </LandingLayout>
</template>
