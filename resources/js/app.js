// import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/index.esm.js';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
    faHome,
    faBox,
    faShoppingCart,
    faUsers,
    faCog,
    faPlus,
    faEdit,
    faTrash,
    faEye,
    faImage,
    faCalendar,
    faMoneyBill,
    faCreditCard,
    faHistory,
    faUser,
    faSignOutAlt,
    faBars,
    faTimes,
    faChevronDown,
    faChevronUp,
    faStar,
    faCheck,
    faExclamationTriangle,
    faSpinner,
    faUpload,
    faDownload,
    faFilter,
    faSearch
} from '@fortawesome/free-solid-svg-icons';

// Add icons to library
library.add(
    faHome, faBox, faShoppingCart, faUsers, faCog,
    faPlus, faEdit, faTrash, faEye, faImage,
    faCalendar, faMoneyBill, faCreditCard, faHistory,
    faUser, faSignOutAlt, faBars, faTimes,
    faChevronDown, faChevronUp, faStar, faCheck,
    faExclamationTriangle, faSpinner, faUpload,
    faDownload, faFilter, faSearch
);

const appName = import.meta.env.VITE_APP_NAME || 'Package Management';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .component('FontAwesomeIcon', FontAwesomeIcon);
            

        // Register global components
        const components = import.meta.glob('./Components/**/*.vue', { eager: true });
        Object.entries(components).forEach(([path, component]) => {
            const componentName = path.split('/').pop().replace('.vue', '');
            vueApp.component(componentName, component.default);
        });

        vueApp.mount(el);
    },
    progress: {
        color: '#4B5563',
        showSpinner: true,
    },
});