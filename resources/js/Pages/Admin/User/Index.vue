<script setup lang="ts">
import { ref, onMounted, nextTick, computed } from "vue";
import { usePage, useForm, Link } from "@inertiajs/vue3";
import AdminItemAction from "@/Components/AdminItemAction.vue";
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";
import { router } from "@inertiajs/vue3";
import DefaultTable from "@/Components/DefaultTable.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import { useScreenSize } from "@/plugins/screen-size";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import InfoTooltip from "@/Components/InfoTooltip.vue";
import UserCard from "./UserCard.vue";
import CustomPageProps from "@/types/model/CustomPageProps";
import { scrollToTop } from "@/plugins/helpers";
import SearchInput from "@/Components/SearchInput.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { useDialogStore } from "@/stores/dialog-store";
import cookieManager from "@/plugins/cookie-manager";
import axios from "axios";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const screenSize = useScreenSize();

const props = defineProps({
    users: Object as () => PaginationModel<UserEntity>,
});

const users = ref<PaginationModel<UserEntity>>({
    ...props.users,
    data: props.users.data.map((user) => ({
        ...user,
        showDeleteModal: false,
    })),
});

const filters = ref({
    page: null,
    search: null,
});

const getQueryParams = () => {
    const urlParams = new URLSearchParams(window.location.search);
    filters.value.page = parseInt(urlParams.get("page")) || 1;
    filters.value.search = urlParams.get("search") || null;
};
getQueryParams();

const queryParams = computed(() => {
    return {
        page: filters.value.page || undefined,
        search: filters.value.search || undefined,
    };
});

function getUsers() {
    router.get("/admin/user", queryParams.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            users.value = {
                ...props.users,
                data: props.users.data.map((user) => ({
                    ...user,
                    showDeleteModal: false,
                })),
            };
            scrollToTop({ id: "main-area" });
            setSearchFocus();
        },
    });
}

const selectedUser = ref(null);
const showDeleteUserDialog = ref(false);

const openDeleteUserDialog = (user) => {
    if (user) {
        selectedUser.value = user;
        showDeleteUserDialog.value = true;
    }
};

const closeDeleteUserDialog = (result = false) => {
    showDeleteUserDialog.value = false;
    if (result) {
        selectedUser.value = null;
        useDialogStore().openSuccessDialog("Data Berhasil Dihapus");
    }
};

const deleteUser = () => {
    if (selectedUser.value) {
        const form = useForm({});
        form.delete(`/admin/user/${selectedUser.value.id}`, {
            onError: (errors) => {
                useDialogStore().openErrorDialog(errors.error);
            },
            onSuccess: () => {
                closeDeleteUserDialog(true);
                getUsers();
            },
        });
    }
};

const page = usePage<CustomPageProps>();

function canEdit(user) {
    return true;
}

function setSearchFocus() {
    nextTick(() => {
        const input = document.getElementById(
            "search-user"
        ) as HTMLInputElement;
        input?.focus({ preventScroll: true });
    });
}

async function exportUsersExcel() {
    try {
        const token = `Bearer ${cookieManager.getItem("access_token")}`;
        await axios
            .get("/admin/user-export", {
                headers: {
                    Authorization: token,
                },
                responseType: "blob",
            })
            .then((response) => {
                useDialogStore().openSuccessDialog(
                    "User data successfully exported."
                );

                // Create a link to download the file
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute("download", "users.xlsx");
                document.body.appendChild(link);
                link.click();
                link.remove();
            });
    } catch (error) {
        useDialogStore().openErrorDialog(
            "Failed to export data. Please try again."
        );
    }
}

onMounted(() => {
    if (page.props.flash?.success) {
        useDialogStore().openSuccessDialog(page.props.flash.success);
    }
    setSearchFocus();
});
</script>

<template>
    <AdminLayout title="Users" :showTitle="true">
        <div class="p-4">
            <div
                class="flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between mb-6"
            >
                <div>
                    <h1 class="text-2xl font-bold mb-1">Users</h1>
                    <p class="text-sm text-gray-600">
                        Total: {{ page.props.users.total }} users
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <SearchInput
                        id="search-user"
                        v-model="filters.search"
                        placeholder="Search..."
                        class="w-full sm:max-w-48"
                        @search="
                            filters.page = 1;
                            getUsers();
                        "
                    />

                    <SecondaryButton @click="exportUsersExcel()">
                        Export
                        <template #prefix>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="16"
                                height="16"
                                viewBox="0 0 16 16"
                                fill="currentColor"
                                class="size-5"
                            >
                                <path
                                    d="M13.75 7.0001V13.0001C13.75 13.3316 13.6183 13.6496 13.3839 13.884C13.1495 14.1184 12.8315 14.2501 12.5 14.2501H3.5C3.16848 14.2501 2.85054 14.1184 2.61612 13.884C2.3817 13.6496 2.25 13.3316 2.25 13.0001V7.0001C2.25 6.66858 2.3817 6.35064 2.61612 6.11622C2.85054 5.8818 3.16848 5.7501 3.5 5.7501H4.75C4.94891 5.7501 5.13968 5.82912 5.28033 5.96977C5.42098 6.11043 5.5 6.30119 5.5 6.5001C5.5 6.69902 5.42098 6.88978 5.28033 7.03043C5.13968 7.17109 4.94891 7.2501 4.75 7.2501H3.75V12.7501H12.25V7.2501H11.25C11.0511 7.2501 10.8603 7.17109 10.7197 7.03043C10.579 6.88978 10.5 6.69902 10.5 6.5001C10.5 6.30119 10.579 6.11043 10.7197 5.96977C10.8603 5.82912 11.0511 5.7501 11.25 5.7501H12.5C12.8315 5.7501 13.1495 5.8818 13.3839 6.11622C13.6183 6.35064 13.75 6.66858 13.75 7.0001ZM6.03062 4.53073L7.25 3.3126V8.5001C7.25 8.69902 7.32902 8.88978 7.46967 9.03043C7.61032 9.17108 7.80109 9.2501 8 9.2501C8.19891 9.2501 8.38968 9.17108 8.53033 9.03043C8.67098 8.88978 8.75 8.69902 8.75 8.5001V3.3126L9.96937 4.5326C10.0391 4.60237 10.122 4.65771 10.2131 4.69546C10.3043 4.73322 10.402 4.75265 10.5006 4.75265C10.5993 4.75265 10.697 4.73322 10.7881 4.69546C10.8793 4.65771 10.9621 4.60237 11.0319 4.5326C11.1016 4.46284 11.157 4.38002 11.1947 4.28886C11.2325 4.19771 11.2519 4.10002 11.2519 4.00135C11.2519 3.90269 11.2325 3.80499 11.1947 3.71384C11.157 3.62269 11.1016 3.53987 11.0319 3.4701L8.53187 0.970103C8.4622 0.900183 8.3794 0.844705 8.28824 0.806851C8.19707 0.768997 8.09934 0.749512 8.00062 0.749512C7.90191 0.749512 7.80418 0.768997 7.71301 0.806851C7.62185 0.844705 7.53905 0.900183 7.46938 0.970103L4.96938 3.4701C4.89961 3.53987 4.84427 3.62269 4.80651 3.71384C4.76876 3.80499 4.74932 3.90269 4.74932 4.00135C4.74932 4.20061 4.82848 4.39171 4.96938 4.5326C5.11027 4.6735 5.30137 4.75265 5.50063 4.75265C5.69988 4.75265 5.89098 4.6735 6.03188 4.5326L6.03062 4.53073Z"
                                    fill="currentColor"
                                />
                            </svg>
                        </template>
                    </SecondaryButton>
                    <PrimaryButton
                        type="button"
                        class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                        @click="$inertia.visit('/admin/user/create')"
                    >
                        Add
                    </PrimaryButton>
                </div>
            </div>

            <!-- Table -->
            <DefaultTable
                v-if="screenSize.is('xl')"
                :isEmpty="users.data.length === 0"
                class="mt-6"
            >
                <template #thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th class="w-24">Actions</th>
                    </tr>
                </template>
                <template #tbody>
                    <tr v-for="(user, index) in users.data" :key="user.id">
                        <td>
                            {{
                                index +
                                1 +
                                (props.users.current_page - 1) *
                                    props.users.per_page
                            }}
                        </td>
                        <td>
                            <Link
                                :href="`/admin/user/${user.id}`"
                                class="flex items-center gap-3 group"
                            >
                                <div class="flex items-center gap-3">
                                    <img
                                        :src="
                                            $getImageUrl(
                                                user.profile_photo_path ??
                                                    user.profile_photo_url
                                            )
                                        "
                                        alt="Foto User"
                                        class="object-cover rounded-full size-8"
                                    />
                                    <div>
                                        <p class="group-hover:underline">
                                            {{ user.name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ user.username }}
                                        </p>
                                    </div>
                                </div>
                            </Link>
                        </td>
                        <td class="whitespace-normal!">
                            {{ user.email ?? "-" }}
                        </td>
                        <td class="whitespace-normal!">
                            {{ user.phone ?? "-" }}
                        </td>
                        <td class="whitespace-normal!">
                            {{ user.roles[0]?.name ?? "-" }}
                        </td>
                        <td>
                            <AdminItemAction
                                v-if="canEdit(user)"
                                @edit="
                                    $inertia.visit(
                                        `/admin/user/${user.id}/edit`
                                    )
                                "
                                @delete="openDeleteUserDialog(user)"
                            />
                            <InfoTooltip
                                v-if="!canEdit(user)"
                                :id="`table-tooltip-hint-${user.id}`"
                                text="User tidak dapat diedit atau dihapus"
                            />
                        </td>
                    </tr>
                </template>
            </DefaultTable>

            <!-- Mobile View -->
            <div v-if="!screenSize.is('xl')" class="flex flex-col gap-3 mt-4">
                <div
                    v-if="users.data.length > 0"
                    class="grid grid-cols-1 gap-3"
                >
                    <UserCard
                        v-for="(user, index) in users.data"
                        :key="user.id"
                        :user="user"
                        @edit="$inertia.visit(`/admin/user/${user.id}/edit`)"
                        @delete="openDeleteUserDialog(user)"
                    />
                </div>
                <div v-else class="flex items-center justify-center py-10">
                    <p class="text-sm text-center text-gray-500">
                        No data found.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="users.total > 0" class="flex flex-col gap-2 mt-4">
                <p class="text-xs text-gray-500 sm:text-sm">
                    Showing {{ users.from }} - {{ users.to }} of
                    {{ users.total }} items
                </p>
                <DefaultPagination
                    :isApi="true"
                    :links="users.links"
                    @change="
                        (page) => {
                            filters.page = page;
                            getUsers();
                        }
                    "
                />
            </div>
        </div>

        <DeleteConfirmationDialog
            :show="showDeleteUserDialog"
            :title="`Delete User <b>${selectedUser?.name}</b>?`"
            :description="`This action cannot be undone.`"
            @close="closeDeleteUserDialog()"
            @delete="deleteUser()"
        />
    </AdminLayout>
</template>
