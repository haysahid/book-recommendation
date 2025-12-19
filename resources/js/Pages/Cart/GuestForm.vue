<script setup lang="ts">
import InputGroup from "@/Components/InputGroup.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useOrderStore } from "@/stores/order-store";
import { useForm } from "@inertiajs/vue3";
import { nextTick, onMounted } from "vue";

const props = defineProps({
    title: {
        type: String || null,
        default: "Customer",
    },
    isEdit: {
        type: Boolean,
        default: false,
    },
    showSubmitButton: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["submit"]);

const orderStore = useOrderStore();

const form = useForm({
    guest_name: orderStore.form.guest_name,
    guest_email: orderStore.form.guest_email,
    guest_phone: orderStore.form.guest_phone,
});

function submit() {
    orderStore.updateForm({
        ...orderStore.form,
        ...form.data(),
    });

    emit("submit");
}

onMounted(() => {
    nextTick(() => {
        const input = document.getElementById("guest-name") as HTMLInputElement;
        input?.focus();
    });
});
</script>

<template>
    <form @submit.prevent="submit" class="w-full">
        <div class="flex flex-col w-full gap-4 gap-y-4">
            <h3 v-if="props.title" class="text-lg font-semibold text-gray-700">
                {{ title }}
            </h3>

            <div class="flex flex-col gap-y-4">
                <InputGroup label="Full Name" id="guest-name">
                    <TextInput
                        id="guest-name"
                        v-model="form.guest_name"
                        type="text"
                        placeholder="Enter full name"
                        required
                        :autofocus="false"
                        autocomplete="name"
                    />
                </InputGroup>

                <InputGroup label="Email" id="email">
                    <TextInput
                        id="email"
                        v-model="form.guest_email"
                        type="email"
                        placeholder="Enter email address"
                        required
                        autocomplete="email"
                    />
                </InputGroup>

                <InputGroup label="Phone Number (WA)" id="phone">
                    <TextInput
                        id="phone"
                        v-model="form.guest_phone"
                        type="text"
                        placeholder="Enter phone number (WA)"
                        required
                        autocomplete="phone"
                    />
                </InputGroup>
            </div>

            <PrimaryButton
                v-if="props.showSubmitButton"
                type="submit"
                class="mt-3"
            >
                {{ isEdit ? "Save" : "Continue" }}
            </PrimaryButton>
        </div>
    </form>
</template>
