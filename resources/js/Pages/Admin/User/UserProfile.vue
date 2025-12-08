<script setup lang="ts">
import DefaultCard from "@/Components/DefaultCard.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import WhatsAppButton from "@/Components/WhatsAppButton.vue";
import { getWhatsAppLink } from "@/plugins/helpers";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    user: {
        type: Object as () => UserEntity,
        required: true,
    },
});
</script>

<template>
    <DefaultCard>
        <div
            class="flex flex-col items-center w-full gap-3 py-2 lg:flex-row lg:py-0"
        >
            <img
                :src="
                    $getImageUrl(
                        props.user.profile_photo_path ??
                            props.user.profile_photo_url
                    )
                "
                alt="Foto Pengguna"
                class="object-cover rounded-full size-[100px] h-fit shrink-0 aspect-square"
            />

            <div
                class="flex flex-col items-center justify-center w-full gap-2 lg:items-start"
            >
                <p class="font-bold text-gray-900 md:text-lg">
                    {{ props.user.name }}
                </p>
                <p v-if="props.user.username" class="text-sm text-gray-600">
                    {{ props.user.username }}
                </p>
                <div
                    class="flex flex-wrap items-start justify-center w-full text-sm text-gray-600 max-lg:flex-col lg:justify-start gap-x-6 gap-y-1"
                >
                    <div
                        v-if="props.user.email"
                        class="flex items-center gap-0.5"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            class="inline-block mr-1 size-5 fill-gray-400"
                        >
                            <path
                                d="M4 20C3.45 20 2.97933 19.8043 2.588 19.413C2.19667 19.0217 2.00067 18.5507 2 18V6C2 5.45 2.196 4.97933 2.588 4.588C2.98 4.19667 3.45067 4.00067 4 4H20C20.55 4 21.021 4.196 21.413 4.588C21.805 4.98 22.0007 5.45067 22 6V18C22 18.55 21.8043 19.021 21.413 19.413C21.0217 19.805 20.5507 20.0007 20 20H4ZM12 12.825C12.0833 12.825 12.171 12.8123 12.263 12.787C12.355 12.7617 12.4423 12.7243 12.525 12.675L19.6 8.25C19.7333 8.16667 19.8333 8.06267 19.9 7.938C19.9667 7.81333 20 7.67567 20 7.525C20 7.19167 19.8583 6.94167 19.575 6.775C19.2917 6.60833 19 6.61667 18.7 6.8L12 11L5.3 6.8C5 6.61667 4.70833 6.61267 4.425 6.788C4.14167 6.96333 4 7.209 4 7.525C4 7.69167 4.03333 7.83767 4.1 7.963C4.16667 8.08833 4.26667 8.184 4.4 8.25L11.475 12.675C11.5583 12.725 11.646 12.7627 11.738 12.788C11.83 12.8133 11.9173 12.8257 12 12.825Z"
                            />
                        </svg>
                        <span>
                            {{ props.user.email }}
                        </span>
                    </div>
                    <div
                        v-if="props.user.phone"
                        class="flex items-center gap-0.5"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            class="inline-block mr-1 size-5 fill-gray-400"
                        >
                            <path
                                d="M20.4868 17.14L16.4218 13.444C16.2298 13.2691 15.9772 13.1758 15.7176 13.1838C15.458 13.1919 15.2117 13.3006 15.0308 13.487L12.6378 15.948C12.0618 15.838 10.9038 15.477 9.71179 14.288C8.51979 13.095 8.15879 11.934 8.05179 11.362L10.5108 8.968C10.6972 8.78712 10.8059 8.54082 10.814 8.2812C10.822 8.02159 10.7287 7.76904 10.5538 7.57699L6.85879 3.51299C6.68384 3.32035 6.44067 3.2035 6.18095 3.18725C5.92122 3.17101 5.66539 3.25665 5.46779 3.42599L3.29779 5.28699C3.1249 5.46051 3.02171 5.69145 3.00779 5.93599C2.99279 6.18599 2.70679 12.108 7.29879 16.702C11.3048 20.707 16.3228 21 17.7048 21C17.9068 21 18.0308 20.994 18.0638 20.992C18.3081 20.9776 18.5387 20.874 18.7118 20.701L20.5718 18.53C20.7418 18.333 20.8281 18.0774 20.8122 17.8177C20.7963 17.558 20.6795 17.3148 20.4868 17.14Z"
                            />
                        </svg>
                        <span>
                            {{ props.user.phone }}
                        </span>
                    </div>
                    <div
                        v-if="props.user.roles[0]?.name"
                        class="flex items-center gap-0.5"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            class="inline-block mr-1 size-5 fill-gray-400"
                        >
                            <path
                                d="M20 4H4C2.897 4 2 4.897 2 6V18C2 19.103 2.897 20 4 20H20C21.103 20 22 19.103 22 18V6C22 4.897 21.103 4 20 4ZM8.715 8C9.866 8 10.715 8.849 10.715 10C10.715 11.151 9.866 12 8.715 12C7.564 12 6.715 11.151 6.715 10C6.715 8.849 7.563 8 8.715 8ZM12.43 16H5V15.535C5 14.162 6.676 12.75 8.715 12.75C10.754 12.75 12.43 14.162 12.43 15.535V16ZM19 15H15V13H19V15ZM19 11H14V9H19V11Z"
                            />
                        </svg>
                        <span>
                            {{ props.user.roles[0]?.name }}
                        </span>
                    </div>
                    <div
                        v-if="props.user.address"
                        class="flex items-center gap-0.5"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            class="inline-block mr-1 size-5 fill-gray-400 shrink-0"
                        >
                            <path
                                d="M12 2C16.6945 2 20.5 5.8055 20.5 10.5C20.5033 12.5106 19.7908 14.4568 18.49 15.99L18.489 15.9905L18.481 16H18.5L13.4555 21.3545C13.2685 21.5529 13.043 21.711 12.7927 21.819C12.5424 21.9271 12.2726 21.9828 12 21.9828C11.7274 21.9828 11.4576 21.9271 11.2074 21.819C10.9571 21.711 10.7315 21.5529 10.5445 21.3545L5.50002 16H5.51902L5.51002 15.99L5.50002 15.9775C5.17673 15.5946 4.88793 15.1838 4.63702 14.75C3.8894 13.4586 3.49711 11.9922 3.50002 10.5C3.50002 5.8055 7.30552 2 12 2ZM12 7.5C11.2044 7.5 10.4413 7.81607 9.8787 8.37868C9.31609 8.94129 9.00002 9.70435 9.00002 10.5C9.00002 11.2956 9.31609 12.0587 9.8787 12.6213C10.4413 13.1839 11.2044 13.5 12 13.5C12.7957 13.5 13.5587 13.1839 14.1213 12.6213C14.6839 12.0587 15 11.2956 15 10.5C15 9.70435 14.6839 8.94129 14.1213 8.37868C13.5587 7.81607 12.7957 7.5 12 7.5Z"
                            />
                        </svg>
                        <span>
                            {{ props.user.address }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex gap-2 mt-2 lg:mt-0 lg:justify-end">
                <Link :href="`/admin/user/${props.user.id}/edit`">
                    <SecondaryButton
                        type="button"
                        @click="$event.stopPropagation()"
                    >
                        Edit
                    </SecondaryButton>
                </Link>
                <Link
                    v-if="props.user.phone"
                    :href="
                        getWhatsAppLink(
                            props.user.phone,
                            'Halo, saya ingin bertanya tentang produk Anda.'
                        )
                    "
                    target="_blank"
                >
                    <WhatsAppButton
                        type="button"
                        @click="$event.stopPropagation()"
                    >
                        Contact
                    </WhatsAppButton>
                </Link>
            </div>
        </div>
    </DefaultCard>
</template>
