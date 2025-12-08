import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { createPinia } from 'pinia';

// My Plugins
import { getImageUrl } from './plugins/helpers';
import formatDate from './plugins/date-formatter';
import { formatCurrency, formatNumber } from './plugins/number-formatter.ts';

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        return pages[`./Pages/${name}.vue`]
    },
    setup({ el, App, props, plugin }) {
        const pinia = createPinia()

        const app = createApp({ render: () => h(App, props) })
            .use(pinia)
            .use(plugin);

        app.config.globalProperties.$getImageUrl = getImageUrl;
        app.config.globalProperties.$formatDate = formatDate;
        app.config.globalProperties.$formatCurrency = formatCurrency;
        app.config.globalProperties.$formatNumber = formatNumber;
        app.mount(el);
    },
})