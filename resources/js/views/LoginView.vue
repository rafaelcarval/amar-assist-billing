<script setup>
import { reactive, ref } from 'vue';

import {
    useRoute,
    useRouter,
} from 'vue-router';

import { useAuthStore } from '../stores/auth';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

const form = reactive({
    email: 'admin@amar.test',
    password: 'Amar@123456',
    remember: false,
});

const error = ref('');

async function submit() {
    error.value = '';

    try {
        await auth.login(form);

        const redirect =
            typeof route.query.redirect === 'string'
                ? route.query.redirect
                : '/customers';

        await router.push(redirect);
    } catch (exception) {
        error.value =
            exception.response?.data
                ?.errors?.email?.[0]
            ?? exception.response?.data
                ?.message
            ?? 'Não foi possível realizar o login.';
    }
}
</script>

<template>
    <main
        class="container d-flex align-items-center justify-content-center"
        style="min-height: 100vh;"
    >
        <div
            class="card border-0 shadow"
            style="width: 100%; max-width: 430px;"
        >
            <div class="card-body p-5">

                <div class="mb-4">
                    <h1 class="h3 fw-bold mb-1">
                        Amar Assist
                    </h1>

                    <p class="text-muted mb-0">
                        Sistema de cobranças
                    </p>
                </div>

                <div
                    v-if="error"
                    class="alert alert-danger"
                >
                    {{ error }}
                </div>

                <form @submit.prevent="submit">

                    <div class="mb-3">
                        <label
                            for="email"
                            class="form-label"
                        >
                            E-mail
                        </label>

                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="form-control"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label
                            for="password"
                            class="form-label"
                        >
                            Senha
                        </label>

                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="form-control"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <div class="form-check mb-4">

                        <input
                            id="remember"
                            v-model="form.remember"
                            class="form-check-input"
                            type="checkbox"
                        >

                        <label
                            class="form-check-label"
                            for="remember"
                        >
                            Manter conectado
                        </label>

                    </div>

                    <button
                        type="submit"
                        class="btn btn-dark w-100"
                        :disabled="auth.loading"
                    >
                        {{
                            auth.loading
                                ? 'Entrando...'
                                : 'Entrar'
                        }}
                    </button>

                </form>

            </div>
        </div>
    </main>
</template>