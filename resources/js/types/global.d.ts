import "@vue/runtime-core";
import { router } from "@inertiajs/vue3";
import { Page } from "@inertiajs/core";
import CustomPageProps from "./model/CustomPageProps";
import { getImageUrl } from "@/plugins/helpers";

declare module "@vue/runtime-core" {
    export interface ComponentCustomProperties {
        $inertia: typeof router;
        $page: Page<CustomPageProps>;
        $getImageUrl: typeof getImageUrl;
    }
}
