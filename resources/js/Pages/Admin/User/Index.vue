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

onMounted(() => {
    if (page.props.flash?.success) {
        useDialogStore().openSuccessDialog(page.props.flash.success);
    }
    setSearchFocus();
});
</script>

<template>
    <AdminLayout title="Users" :showTitle="true">
        <DefaultCard :isMain="true">
            <div class="flex items-center justify-between gap-4">
                <PrimaryButton
                    type="button"
                    class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                    @click="$inertia.visit('/admin/user/create')"
                >
                    Add
                </PrimaryButton>
                <SearchInput
                    id="search-user"
                    v-model="filters.search"
                    placeholder="Search users..."
                    class="max-w-48"
                    @search="
                        filters.page = 1;
                        getUsers();
                    "
                />
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
                                    <p class="group-hover:underline">
                                        {{ user.name }}
                                    </p>
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
        </DefaultCard>

        <DeleteConfirmationDialog
            :show="showDeleteUserDialog"
            :title="`Delete User <b>${selectedUser?.name}</b>?`"
            :description="`This action cannot be undone.`"
            @close="closeDeleteUserDialog()"
            @delete="deleteUser()"
        />
    </AdminLayout>
</template>
