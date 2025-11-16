<script setup lang="ts">
import LandingMainContainer from "@/Components/LandingMainContainer.vue";
import LandingLayout from "@/Layouts/LandingLayout.vue";
import useBookService from "@/services/book-service";
import { computed, onMounted, ref } from "vue";
import BookCard from "../Admin/Book/BookCard.vue";
import ThreeDotsLoading from "@/Components/ThreeDotsLoading.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    book: {
        type: Object as () => BookEntity,
        required: true,
    },
    relatedBooks: {
        type: Array as () => BookEntity[],
        required: false,
        default: () => [],
    },
});

const bookDetail = ref<BookDetailModel | null>(null);
const loadBookDetailStatus = ref(null);

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
    <LandingLayout :title="book.title">
        <LandingMainContainer class="py-6 sm:py-12">
            <!-- Book -->
            <div class="grid gap-4 md:gap-8 lg:gap-12 md:grid-cols-2">
                <!-- Image -->
                <div class="">
                    <div
                        class="h-96 sm:h-[540px] w-full transition-all duration-300 ease-in-out"
                    >
                        <img
                            :src="book.image"
                            :alt="`Cover image of ${book.title}`"
                            class="w-full h-full object-contain rounded hover:scale-105 transition-transform duration-300 ease-in-out shrink-0"
                        />
                    </div>
                </div>

                <!-- Details -->
                <div class="py-6 sm:py-12">
                    <h1 class="text-3xl font-bold">{{ book.title }}</h1>
                    <p class="text-lg text-muted-foreground mt-1">
                        by {{ book.author }}
                    </p>

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
                    <div class="flex items-center gap-4 mt-6">
                        <a
                            :href="`https://www.gramedia.com/products/${book.slug}`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-block w-full md:w-auto"
                        >
                            <PrimaryButton> Buy on Gramedia </PrimaryButton>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Related Books -->
            <div v-if="relatedBooks.length" class="mt-12">
                <h2 class="text-2xl font-bold mb-6">Related Books</h2>
                <div
                    class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
                >
                    <a
                        v-for="(relatedBook, index) in relatedBooks"
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
