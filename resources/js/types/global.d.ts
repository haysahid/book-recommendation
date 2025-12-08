import "@vue/runtime-core";
import { router } from "@inertiajs/vue3";
import { Page } from "@inertiajs/core";
import { formatCurrency, formatNumber } from "@/plugins/number-formatter";
import CustomPageProps from "./model/CustomPageProps";
import { getImageUrl } from "@/plugins/helpers";

declare module "@vue/runtime-core" {
    export interface ComponentCustomProperties {
        $inertia: typeof router;
        $page: Page<CustomPageProps>;
        $formatDate: typeof formatDate;
        $getImageUrl: typeof getImageUrl;
        $formatCurrency: typeof formatCurrency;
        $formatNumber: typeof formatNumber;
    }
}
