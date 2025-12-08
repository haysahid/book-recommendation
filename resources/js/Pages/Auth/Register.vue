<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import LandingHeader from "@/Components/LandingHeader.vue";
import { watch } from "vue";

const props = defineProps({
    status: String,
});

const form = useForm({
    name: "",
    username: "",
    password: "",
    password_confirmation: "",
    terms: false,
});

watch(
    () => form.username,
    () => {
        if (form.username === "") {
            form.errors.username = null;
        } else if (!/^[a-zA-Z0-9_.]+$/.test(form.username)) {
            form.errors.username =
                "Username can only contain letters, numbers, underscores (_), and dots (.).";
        } else if (
            form.username.startsWith(".") ||
            form.username.endsWith(".")
        ) {
            form.errors.username =
                "Username cannot start or end with a dot (.).";
        } else if (/\.\./.test(form.username)) {
            form.errors.username =
                "Username cannot contain consecutive dots (..).";
        } else if (form.username.length < 3) {
            form.errors.username = "Username must be at least 3 characters.";
        } else if (!/[a-zA-Z]/.test(form.username)) {
            form.errors.username =
                "Username must contain at least one alphabet.";
        } else {
            form.errors.username = null;
        }
    }
);

watch(
    () => form.password,
    () => {
        if (form.password === "") {
            form.errors.password = null;
        } else if (form.password.length < 8) {
            form.errors.password = "Password must be at least 8 characters.";
        } else {
            form.errors.password = null;
        }
    }
);

watch(
    () => form.password_confirmation,
    () => {
        if (form.password_confirmation === "") {
            form.errors.password_confirmation = null;
        } else if (form.password_confirmation !== form.password) {
            form.errors.password_confirmation = "Passwords do not match.";
        } else {
            form.errors.password_confirmation = null;
        }
    }
);

function validate() {
    if (!/^[a-zA-Z0-9_.]+$/.test(form.username)) {
        form.errors.username =
            "Username can only contain letters, numbers, underscores (_), and dots (.).";
        return false;
    } else if (form.username.startsWith(".") || form.username.endsWith(".")) {
        form.errors.username = "Username cannot start or end with a dot (.).";
        return false;
    } else if (/\.\./.test(form.username)) {
        form.errors.username = "Username cannot contain consecutive dots (..).";
        return false;
    } else if (form.username === "") {
        form.errors.username = "Username is required.";
        return false;
    } else if (form.username.length < 3) {
        form.errors.username = "Username must be at least 3 characters.";
        return false;
    } else if (!/[a-zA-Z]/.test(form.username)) {
        form.errors.username = "Username must contain at least one alphabet.";
        return false;
    }

    if (form.password.length < 8) {
        form.errors.password = "Password must be at least 8 characters.";
        return false;
    }

    if (form.password !== form.password_confirmation) {
        form.errors.password_confirmation = "Passwords do not match.";
        return false;
    }

    return true;
}

const submit = () => {
    if (validate()) {
        form.post("/register", {
            onFinish: () => form.reset("password", "password_confirmation"),
        });
    }
};
</script>

<template>
    <Head title="Register" />

    <AuthenticationCard containerClass="max-w-sm!">
        <LandingHeader
            :invertColor="true"
            class="border-b-0! mb-8 md:mb-6 bg-transparent! bg-none fixed! top-2 left-0 right-0"
        />

        <template #logo>
            <div
                class="flex flex-col items-center justify-center gap-2 mb-6 sm:flex-row"
            >
                <h1 class="text-2xl font-bold text-gray-800 sm:block">
                    Register
                </h1>
            </div>
        </template>

        <div
            v-if="props.status"
            class="mb-4 text-sm font-medium text-center text-green-600"
        >
            {{ props.status }}
        </div>

        <div
            v-if="form.errors.access"
            class="mb-4 text-sm font-medium text-center text-red-600"
        >
            {{ form.errors.access }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    placeholder="Enter Fullname"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="name"
                    :error="form.errors.name"
                />
            </div>

            <div class="mt-4">
                <InputLabel for="username" value="Username" />
                <TextInput
                    id="username"
                    v-model="form.username"
                    type="text"
                    placeholder="Enter Username"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                    :error="form.errors.username"
                />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    placeholder="Enter Password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                    :error="form.errors.password"
                />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    placeholder="Enter Password Confirmation"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                    :error="form.errors.password_confirmation"
                />
            </div>

            <div
                v-if="$page.props.auth.hasTermsAndPrivacyPolicyFeature"
                class="mt-4"
            >
                <InputLabel for="terms">
                    <div class="flex items-start">
                        <Checkbox
                            id="terms"
                            v-model:checked="form.terms"
                            name="terms"
                            required
                            class="mt-1"
                        />

                        <div class="ms-2">
                            I agree to the
                            <a
                                target="_blank"
                                href="/terms"
                                class="underline text-sm text-gray-600 hover:text-primary rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >Terms of Service</a
                            >
                            and
                            <a
                                target="_blank"
                                href="/policy"
                                class="underline text-sm text-gray-600 hover:text-primary rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >Privacy Policy</a
                            >
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.terms" />
                </InputLabel>
            </div>

            <div class="flex flex-col items-center justify-center mt-4">
                <PrimaryButton
                    class="w-full px-4 py-2"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </PrimaryButton>

                <div class="mt-3 text-sm text-gray-500">
                    Already registered?
                    <a href="/login" class="text-primary hover:underline">
                        Login
                    </a>
                </div>
            </div>
        </form>
    </AuthenticationCard>
</template>
