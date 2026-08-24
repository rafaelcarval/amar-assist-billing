<script setup>
import { useRouter } from 'vue-router';

import { useAuthStore } from './stores/auth';

const router = useRouter();
const auth = useAuthStore();

async function logout() {
    await auth.logout();

    await router.push({
        name: 'login',
    });
}
</script>

<template>
    <div class="min-vh-100 bg-light">

        <nav
            v-if="auth.isAuthenticated"
            class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm"
        >
            <div class="container">

                <RouterLink
                    class="navbar-brand fw-semibold"
                    :to="{ name: 'customers' }"
                >
                    Amar Assist Billing
                </RouterLink>

                <div class="navbar-nav me-auto">

                    <RouterLink
                        class="nav-link"
                        :to="{ name: 'customers' }"
                    >
                        Clientes
                    </RouterLink>

                    <RouterLink
                        class="nav-link"
                        :to="{ name: 'charges' }"
                    >
                        Cobranças
                    </RouterLink>

                </div>

                <div class="d-flex align-items-center gap-3">

                    <span class="text-light small">
                        {{ auth.user?.name }}
                    </span>

                    <button
                        type="button"
                        class="btn btn-outline-light btn-sm"
                        @click="logout"
                    >
                        Sair
                    </button>

                </div>

            </div>
        </nav>

        <RouterView />

    </div>
</template>

<style scoped>
.router-link-active {
    font-weight: 600;
}
</style>