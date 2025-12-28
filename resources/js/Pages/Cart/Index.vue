<script setup lang="ts">
import LandingSection from "@/Components/LandingSection.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import LandingLayout from "@/Layouts/LandingLayout.vue";
import { Link, usePage } from "@inertiajs/vue3";
import { useCartStore } from "@/stores/cart-store";
import axios from "axios";
import CartGroup from "./CartGroup.vue";
import CartForm from "./CartForm.vue";
import OrderForm from "./OrderForm.vue";
import { useOrderStore } from "@/stores/order-store";
import CustomPageProps from "@/types/model/CustomPageProps";
import { computed, ref } from "vue";
import GuestForm from "./GuestForm.vue";
import DetailRow from "@/Components/DetailRow.vue";

const cartStore = useCartStore();

function syncCart() {
    if (cartStore.groups.length > 0) {
        let cartGroups = cartStore.groups;

        for (const group of cartGroups) {
            delete group.store;

            for (const item of group.items) {
                // Remove the 'store' property from each item
                if (item.store) {
                    delete item.store;
                }

                // Remove the 'variant' property from each item
                if (item.book) {
                    delete item.book;
                }
            }
        }

        const syncCart = JSON.parse(JSON.stringify(cartGroups));

        axios
            .post("/api/sync-cart", {
                cart_groups: syncCart,
            })
            .then((response) => {
                cartStore.updateAllGroups(response.data.result);
            });
    }
}
syncCart();

const page = usePage<CustomPageProps>();
const orderStore = useOrderStore();

const customer = computed(() => {
    return page.props.auth.user
        ? {
              name: page.props.auth.user.name,
              username: page.props.auth.user.username,
              email: page.props.auth.user.email,
              phone: page.props.auth.user.phone,
          }
        : {
              name: orderStore.form.guest_name,
              email: orderStore.form.guest_email,
              phone: orderStore.form.guest_phone,
          };
});

const showGuestForm = ref(false);
</script>

<template>
    <LandingLayout :title="`Cart (${cartStore.items.length})`">
        <div
            class="p-6 sm:p-12 md:px-[100px] md:py-[60px] flex flex-col gap-2 sm:gap-3"
            :class="{
                'min-h-[60vh] items-center justify-center gap-4':
                    cartStore.items.length == 0,
            }"
        >
            <h1 class="text-xl font-bold text-start sm:text-center">
                {{
                    cartStore.items.length > 0
                        ? `Cart (${cartStore.items.length} item)`
                        : "Cart is Empty"
                }}
            </h1>
            <div
                v-if="cartStore.items.length == 0"
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
                Double-check before placing your order.
            </p>
        </div>

        <div
            data-aos="fade-up"
            data-aos-duration="600"
            class="p-6 pt-0! sm:p-12 md:p-[100px] flex flex-col gap-4 sm:gap-12"
        >
            <LandingSection
                v-if="cartStore.groups.length > 0"
                class="items-start! justify-start!"
            >
                <div
                    class="flex flex-col items-center justify-center w-full gap-5 mx-auto lg:flex-row lg:items-start sm:gap-8 max-w-7xl"
                >
                    <!-- Cart Items -->
                    <div class="flex flex-col w-full gap-4">
                        <CartGroup
                            v-for="(cartGroup, index) in cartStore.groups"
                            :key="index"
                            :cartGroup="cartGroup"
                            :showStoreInfo="false"
                        />
                    </div>

                    <!-- Detail Order -->
                    <div class="flex flex-col w-full gap-6 lg:max-w-sm">
                        <div
                            class="flex flex-col w-full p-4 outline -outline-offset-1 outline-gray-300 rounded-2xl gap-y-4"
                        >
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-gray-800">
                                    Customer Data
                                </h3>
                                <!-- <button
                                    v-if="!page.props.auth.user"
                                    class="text-sm text-blue-500 hover:underline"
                                    @click="showGuestForm = true"
                                >
                                    Ubah
                                </button> -->
                            </div>
                            <template v-if="page.props.auth.user">
                                <DetailRow name="Nama" :value="customer.name" />
                                <DetailRow
                                    name="Username"
                                    :value="customer.username"
                                />
                                <DetailRow
                                    name="Email"
                                    :value="customer.email"
                                />
                                <DetailRow
                                    name="Phone Number"
                                    :value="customer.phone"
                                />
                            </template>

                            <GuestForm
                                v-else
                                :title="null"
                                :show-submit-button="false"
                                @close="showGuestForm = false"
                                class="mb-1"
                            />
                        </div>

                        <OrderForm />
                    </div>
                </div>
            </LandingSection>
        </div>
    </LandingLayout>
</template>
