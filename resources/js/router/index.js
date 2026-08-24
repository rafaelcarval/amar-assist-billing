import {
    createRouter,
    createWebHistory,
} from 'vue-router';

import { useAuthStore } from '../stores/auth';

import LoginView from '../views/LoginView.vue';
import CustomersView from '../views/CustomersView.vue';
import ChargesView from '../views/ChargesView.vue';

const router = createRouter({
    history: createWebHistory('/app/'),

    routes: [
        {
            path: '/',
            redirect: {
                name: 'customers',
            },
        },

        {
            path: '/login',
            name: 'login',
            component: LoginView,

            meta: {
                guestOnly: true,
            },
        },

        {
            path: '/customers',
            name: 'customers',
            component: CustomersView,

            meta: {
                requiresAuth: true,
            },
        },

        {
            path: '/charges',
            name: 'charges',
            component: ChargesView,

            meta: {
                requiresAuth: true,
            },
        },

        {
            path: '/:pathMatch(.*)*',
            redirect: '/',
        },
    ],
});

router.beforeEach(
    async (to) => {
        const auth = useAuthStore();

        await auth.initialize();

        if (
            to.meta.requiresAuth
            && !auth.isAuthenticated
        ) {
            return {
                name: 'login',

                query: {
                    redirect: to.fullPath,
                },
            };
        }

        if (
            to.meta.guestOnly
            && auth.isAuthenticated
        ) {
            return {
                name: 'customers',
            };
        }
    }
);

export default router;