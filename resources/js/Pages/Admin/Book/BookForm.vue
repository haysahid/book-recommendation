<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ImageInput from "@/Components/ImageInput.vue";
import InputGroup from "@/Components/InputGroup.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import CustomPageProps from "@/types/model/CustomPageProps";
import { useDialogStore } from "@/stores/dialog-store";
import Switch from "@/Components/Switch.vue";
import DropdownSearchInputMultiple from "@/Components/DropdownSearchInputMultiple.vue";
import InfoTooltip from "@/Components/InfoTooltip.vue";

const props = defineProps({
    book: {
        type: Object as () => BookEntity | null,
        default: null,
    },
    categories: {
        type: Array as () => CategoryEntity[],
        default: () => [],
    },
    isDialog: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["onSubmitted", "close"]);

type BookForm = {
    title: string | null;
    author: string | null;
    image: string | File | null;
    slice_price: number | null;
    discount: number | null;
    is_oos: boolean | null;
    sku: string | null;
    format: string | null;
    store_name: string | null;
    isbn: string | null;

    // Relationships
    categories: CategoryEntity[] | null;
};

const form = useForm<BookForm>({
    title: props.book?.title || null,
    author: props.book?.author || null,
    image: props.book?.image || null,
    slice_price: props.book?.slice_price || null,
    discount: props.book?.discount || null,
    is_oos: props.book?.is_oos || false,
    sku: props.book?.sku || null,
    format: props.book?.format || null,
    store_name: props.book?.store_name || null,
    isbn: props.book?.isbn || null,

    // Relationships
    categories: props.book?.categories || null,
});

function validate() {
    return true;
}

const submit = () => {
    if (!validate()) return;

    if (props.book?.id) {
        form.transform((data) => {
            const formData = new FormData();
            formData.append("_method", "PUT");
            Object.keys(data).forEach((key) => {
                if (key === "image" && data[key] instanceof File) {
                    formData.append("image", data[key] as File);
                    return;
                }

                if (key === "categories") {
                    for (const category of data.categories || []) {
                        formData.append("categories[]", category.id.toString());
                    }

                    return;
                }

                formData.append(key, data[key]);
            });

            return formData;
        }).post(`/admin/book/${props.book.id}`, {
            onError: (errors) => {
                useDialogStore().openErrorDialog(errors.error);
            },
        });
    } else {
        form.transform((data) => {
            return {
                ...data,
                profile_photo: data.image instanceof File ? data.image : null,
                categories:
                    data.categories?.map((category) => category.id) || [],
                is_dialog: props.isDialog ? 1 : 0,
            };
        }).post("/admin/book", {
            onError: (errors) => {
                form.errors = errors;
                useDialogStore().openErrorDialog(
                    errors.error ?? "Please check the form for errors."
                );
            },
            onSuccess: () => {
                if (props.isDialog) emit("onSubmitted", form.title);
                form.reset();
            },
        });
    }
};
</script>

<template>
    <form @submit.prevent="submit">
        <div class="flex flex-col items-start gap-4">
            <div class="flex flex-col w-full gap-y-4 gap-x-6 sm:flex-row">
                <div class="flex flex-col w-full max-w-3xl gap-4">
                    <!-- Title -->
                    <InputGroup id="title" label="Title" required>
                        <TextInput
                            id="title"
                            v-model="form.title"
                            type="text"
                            placeholder="Enter title"
                            required
                            :autofocus="true"
                            :error="form.errors.title"
                            @update:modelValue="form.errors.title = null"
                        />
                    </InputGroup>

                    <!-- Author -->
                    <InputGroup id="author" label="Author" required>
                        <TextInput
                            id="author"
                            v-model="form.author"
                            type="text"
                            placeholder="Enter author"
                            required
                            :error="form.errors.author"
                            @update:modelValue="form.errors.author = null"
                        />
                    </InputGroup>

                    <!-- Slice Price -->
                    <InputGroup id="slice_price" label="Price" required>
                        <TextInput
                            id="slice_price"
                            v-model="form.slice_price"
                            type="number"
                            placeholder="Enter price"
                            required
                            :error="form.errors.slice_price"
                            @update:modelValue="form.errors.slice_price = null"
                        />
                    </InputGroup>

                    <!-- Discount -->
                    <InputGroup id="discount" label="Discount">
                        <TextInput
                            id="discount"
                            v-model="form.discount"
                            type="number"
                            placeholder="Enter discount"
                            :error="form.errors.discount"
                            @update:modelValue="form.errors.discount = null"
                        />
                    </InputGroup>

                    <!-- Is OOS -->
                    <InputGroup id="is_oos" label="Is Out of Stock" required>
                        <Switch
                            id="is_oos"
                            v-model="form.is_oos"
                            :error="form.errors.is_oos"
                            @update:modelValue="form.errors.is_oos = null"
                        />

                        <template #suffix>
                            <InfoTooltip
                                id="is-oos-info"
                                text="
                                Indicates whether the book is currently out of stock.
                            "
                            />
                        </template>
                    </InputGroup>

                    <!-- SKU -->
                    <InputGroup id="sku" label="SKU">
                        <TextInput
                            id="sku"
                            v-model="form.sku"
                            type="text"
                            placeholder="Enter SKU"
                            :error="form.errors.sku"
                            @update:modelValue="form.errors.sku = null"
                        />
                    </InputGroup>

                    <!-- Image -->
                    <InputGroup id="image" label="Image">
                        <ImageInput
                            id="image"
                            v-model="form.image"
                            type="file"
                            accept="image/*"
                            placeholder="Upload profile photo"
                            width="max-w-[120px]"
                            height="h-[120px]"
                            :error="form.errors.image"
                            @update:modelValue="form.errors.image = null"
                        />
                    </InputGroup>
                </div>
                <div class="flex flex-col w-full max-w-3xl gap-4">
                    <!-- Format -->
                    <InputGroup id="format" label="Format">
                        <TextInput
                            id="format"
                            v-model="form.format"
                            type="text"
                            placeholder="Enter format"
                            :error="form.errors.format"
                            @update:modelValue="form.errors.format = null"
                        />
                    </InputGroup>

                    <!-- Store Name -->
                    <InputGroup id="store_name" label="Store Name">
                        <TextInput
                            id="store_name"
                            v-model="form.store_name"
                            type="text"
                            placeholder="Enter store name"
                            :error="form.errors.store_name"
                            @update:modelValue="form.errors.store_name = null"
                        />
                    </InputGroup>

                    <!-- ISBN -->
                    <InputGroup id="isbn" label="ISBN">
                        <TextInput
                            id="isbn"
                            v-model="form.isbn"
                            type="text"
                            placeholder="Enter ISBN"
                            :error="form.errors.isbn"
                            @update:modelValue="form.errors.isbn = null"
                        />
                    </InputGroup>

                    <!-- Categories -->
                    <InputGroup id="categories" label="Categories">
                        <DropdownSearchInputMultiple
                            id="categories"
                            :modelValue="
                                form.categories.map((c) => {
                                    return {
                                        label: c.title,
                                        value: c.id,
                                    };
                                })
                            "
                            :options="
                                props.categories.map((category) => {
                                    return {
                                        label: category.title,
                                        value: category.id,
                                    };
                                })
                            "
                            :searchable="true"
                            placeholder="Select categories"
                            :error="form.errors.categories"
                            @update:modelValue="
                                (options) => {
                                    form.categories = options.map((option) =>
                                        categories.find(
                                            (category) =>
                                                category.id === option.value
                                        )
                                    );
                                }
                            "
                        />
                    </InputGroup>
                </div>
            </div>

            <div class="flex items-center gap-4 mt-4">
                <PrimaryButton type="submit"> Save </PrimaryButton>
                <SecondaryButton
                    v-if="!isDialog"
                    type="button"
                    @click="$inertia.visit('/admin/book')"
                >
                    Back
                </SecondaryButton>
                <SecondaryButton v-else type="button" @click="emit('close')">
                    Cancel
                </SecondaryButton>
            </div>
        </div>
    </form>
</template>
