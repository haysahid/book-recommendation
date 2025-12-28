import "@vue/runtime-core";
import { router } from "@inertiajs/vue3";
import { Page } from "@inertiajs/core";
import { formatCurrency, formatNumber } from "@/plugins/number-formatter";
import CustomPageProps from "./model/CustomPageProps";
import { getImageUrl } from "@/plugins/helpers";

declare global {
    interface Window {
        snap?: {
            pay: (
                token: string,
                callbacks: {
                    onSuccess?: (result: any) => void;
                    onPending?: (result: any) => void;
                    onError?: (result: any) => void;
                    onClose?: () => void;
                }
            ) => void;
        };
    }
}

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
