<script setup lang="ts">
import InputError from "@/Components/InputError.vue";
import InputGroup from "@/Components/InputGroup.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Rating from "@/Components/Rating.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import useBookService from "@/services/book-service";
import { useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const props = defineProps({
    transactionItem: {
        type: Object as () => TransactionItemEntity,
        required: true,
    },
});

const emit = defineEmits(["submitted", "close"]);

const form = useForm({
    rating: null,
    review: null,
});

const submitReviewStatus = ref(null);

const bookService = useBookService();

watch(
    () => form.rating,
    (newStatus) => {
        form.clearErrors("rating");
    }
);

const validate = () => {
    form.clearErrors();
    if (!form.rating) {
        form.setError("rating", "Rating is required.");
        return false;
    }
    return true;
};

const submit = async () => {
    if (!validate()) return;

    await bookService.addBookReview(
        {
            transactionItemId: props.transactionItem.id,
            rating: form.rating,
            review: form.review,
        },
        {
            onSuccess: () => {
                form.reset();
                emit("submitted");
            },
            onChangeStatus: (status: string) => {
                submitReviewStatus.value = status;
            },
        }
    );
};
</script>

<template>
    <div class="w-full">
        <h3 class="text-lg font-semibold mb-6">Add Rating & Review</h3>

        <form @submit.prevent="submit">
            <div class="flex flex-col items-center gap-4">
                <div class="flex flex-col items-center">
                    <Rating
                        :rating="form.rating ?? 0"
                        :editable="true"
                        @update:rating="(value) => (form.rating = value)"
                    />
                    <InputError
                        :message="form.errors.rating"
                        class="px-2 mt-1"
                    />
                </div>

                <InputGroup
                    :id="`review_${props.transactionItem.id}`"
                    label="Review"
                >
                    <TextAreaInput
                        v-model="form.review"
                        :id="`review_${props.transactionItem.id}`"
                        :name="`review_${props.transactionItem.id}`"
                        placeholder="Write your review here..."
                        :rows="4"
                        :error="form.errors.review"
                    />
                </InputGroup>

                <div class="flex items-center gap-4 mt-4">
                    <SecondaryButton type="button" @click="emit('close')">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton type="submit"> Save </PrimaryButton>
                </div>
            </div>
        </form>
    </div>
</template>
