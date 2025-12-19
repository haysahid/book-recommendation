<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ImageInput from "@/Components/ImageInput.vue";
import InputGroup from "@/Components/InputGroup.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import CustomPageProps from "@/types/model/CustomPageProps";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import { useDialogStore } from "@/stores/dialog-store";
import { watch } from "vue";

const props = defineProps({
    user: {
        type: Object as () => UserEntity | null,
        default: null,
    },
    isDialog: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["onSubmitted", "close"]);

const page = usePage<CustomPageProps>();
const roles = page.props.roles;

type UserForm = {
    name: string | null;
    username: string | null;
    email: string | null;
    password: string | null;
    password_confirmation: string | null;
    phone: string | null;
    address: string | null;
    profile_photo_path: string | File | null;

    // Relationships
    role: RoleEntity | null;
};

const form = useForm<UserForm>({
    name: props.user?.name || null,
    username: props.user?.username || null,
    email: props.user?.email || null,
    password: null,
    password_confirmation: null,
    phone: props.user?.phone || null,
    address: props.user?.address || null,
    profile_photo_path: props.user?.profile_photo_path || null,

    // Relationships
    role: props.user?.roles[0] || null,
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
    if (!validate()) return;

    if (props.user?.id) {
        form.transform((data) => {
            const formData = new FormData();
            Object.keys(data).forEach((key) => {
                if (
                    key === "profile_photo_path" &&
                    !(data[key] instanceof File)
                ) {
                    return;
                }

                if (data[key] === null || data[key] === undefined) {
                    return;
                }

                if (key === "profile_photo_path" && data[key] instanceof File) {
                    formData.append("profile_photo", data[key] as File);
                    return;
                }

                if (key === "role") {
                    formData.append("role", data.role?.name);
                    return;
                }

                formData.append(key, data[key]);
            });

            return formData;
        }).post(`/admin/user/${props.user.id}`, {
            onError: (errors) => {
                useDialogStore().openErrorDialog(errors.error);
            },
        });
    } else {
        form.transform((data) => {
            return {
                ...data,
                profile_photo:
                    data.profile_photo_path instanceof File
                        ? data.profile_photo_path
                        : null,
                role: data.role?.name,
                is_dialog: props.isDialog ? 1 : 0,
            };
        }).post("/admin/user", {
            onError: (errors) => {
                form.errors = errors;
                useDialogStore().openErrorDialog(
                    errors.error ?? "Please check the form for errors."
                );
            },
            onSuccess: () => {
                if (props.isDialog) emit("onSubmitted", form.name);
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
                    <!-- Name -->
                    <InputGroup id="name" label="Full Name" required>
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Enter full name"
                            required
                            :autofocus="true"
                            :error="form.errors.name"
                            @update:modelValue="form.errors.name = null"
                        />
                    </InputGroup>

                    <!-- Username -->
                    <InputGroup id="username" label="Username" required>
                        <TextInput
                            id="username"
                            v-model="form.username"
                            type="text"
                            placeholder="Enter username"
                            required
                            :error="form.errors.username"
                            @update:modelValue="form.errors.username = null"
                        />
                    </InputGroup>

                    <!-- Email -->
                    <InputGroup id="email" label="Email" required>
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="Enter email"
                            required
                            :error="form.errors.email"
                            @update:modelValue="form.errors.email = null"
                        />
                    </InputGroup>

                    <!-- Phone -->
                    <InputGroup id="phone" label="Phone Number" required>
                        <TextInput
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            placeholder="Enter phone number"
                            required
                            :error="form.errors.phone"
                            @update:modelValue="form.errors.phone = null"
                        />
                    </InputGroup>

                    <!-- Address -->
                    <InputGroup id="address" label="Address">
                        <TextAreaInput
                            id="address"
                            v-model="form.address"
                            type="text"
                            placeholder="Enter address"
                            autocomplete="address"
                            :rows="1"
                            :error="form.errors.address"
                            @update:modelValue="form.errors.address = null"
                        />
                    </InputGroup>

                    <!-- Profile Photo -->
                    <InputGroup id="profile_photo_path" label="Profile Photo">
                        <ImageInput
                            id="profile_photo_path"
                            v-model="form.profile_photo_path"
                            type="file"
                            accept="image/*"
                            placeholder="Upload profile photo"
                            width="max-w-[120px]"
                            height="h-[120px]"
                            :error="form.errors.profile_photo_path"
                            @update:modelValue="
                                form.errors.profile_photo_path = null
                            "
                        />
                    </InputGroup>
                </div>
                <div class="flex flex-col w-full max-w-3xl gap-4">
                    <!-- Role -->
                    <InputGroup id="role_id" label="Role" required>
                        <DropdownSearchInput
                            id="role_id"
                            :modelValue="
                                form.role
                                    ? {
                                          label: form.role?.name,
                                          value: form.role?.name,
                                      }
                                    : null
                            "
                            :options="
                                roles.map((role) => ({
                                    label: role.name,
                                    value: role.name,
                                }))
                            "
                            :searchable="true"
                            required
                            placeholder="Select role"
                            :error="form.errors.role"
                            @update:modelValue="
                                (value) => {
                                    form.errors.role = null;
                                    form.role = value
                                        ? roles.find(
                                              (role) =>
                                                  role.name === value.value
                                          )
                                        : null;
                                }
                            "
                        />
                    </InputGroup>

                    <!-- Password -->
                    <InputGroup id="password" label="Password">
                        <TextInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="Enter password"
                            :error="form.errors.password"
                            @update:modelValue="form.errors.password = null"
                        />
                    </InputGroup>

                    <!-- Confirm Password -->
                    <InputGroup
                        id="password_confirmation"
                        label="Confirm Password"
                    >
                        <TextInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="Enter password confirmation"
                            :error="form.errors.password_confirmation"
                            @update:modelValue="
                                form.errors.password_confirmation = null
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
                    @click="$inertia.visit('/admin/user')"
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
