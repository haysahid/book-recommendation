import { ref, watch } from "vue";
import { defineStore } from "pinia";

export const useOrderStore = defineStore("order", () => {
    const form = ref(
        localStorage.getItem("order_form")
            ? (JSON.parse(
                  localStorage.getItem("order_form")
              ) as OrderDetailFormModel)
            : ({} as OrderDetailFormModel)
    );

    watch(
        form,
        (newForm) => {
            localStorage.setItem("order_form", JSON.stringify(newForm));
        },
        { deep: true }
    );

    return {
        form,
    };
});
