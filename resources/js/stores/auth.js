import { defineStore } from 'pinia';

import api from '../lib/http';

export const useAuthStore = defineStore(
    'auth',
    {
        state: () => ({
            user: null,
            initialized: false,
            loading: false,
        }),

        getters: {
            isAuthenticated:
                (state) => Boolean(state.user),
        },

        actions: {
            async initialize() {
                if (this.initialized) {
                    return;
                }

                try {
                    await this.fetchUser();
                } catch {
                    this.user = null;
                } finally {
                    this.initialized = true;
                }
            },

            async fetchUser() {
                const response = await api.get(
                    '/api/user'
                );

                this.user = response.data.data;

                return this.user;
            },

            async login(credentials) {
                this.loading = true;

                try {
                    await api.get(
                        '/sanctum/csrf-cookie'
                    );

                    const response = await api.post(
                        '/login',
                        credentials
                    );

                    this.user = response.data.data;
                    this.initialized = true;

                    return this.user;
                } finally {
                    this.loading = false;
                }
            },

            async logout() {
                try {
                    await api.post('/logout');
                } finally {
                    this.user = null;
                    this.initialized = true;
                }
            },
        },
    }
);