<script setup>
import {
    onMounted,
    reactive,
    ref,
} from 'vue';

import api from '../lib/http';

const customers = ref([]);
const loading = ref(false);
const message = ref('');
const error = ref('');

const pagination = reactive({
    currentPage: 1,
    lastPage: 1,
});

const filters = reactive({
    name: '',
    status: '',
    document: '',
});

function formatDocument(document) {
    if (!document) {
        return '';
    }

    const value = String(document);

    if (value.length === 11) {
        return value.replace(
            /(\d{3})(\d{3})(\d{3})(\d{2})/,
            '$1.$2.$3-$4'
        );
    }

    if (value.length === 14) {
        return value.replace(
            /(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/,
            '$1.$2.$3/$4-$5'
        );
    }

    return value;
}

async function loadCustomers(page = 1) {
    loading.value = true;
    error.value = '';

    try {
        const response = await api.get(
            '/api/customers',
            {
                params: {
                    page,

                    name:
                        filters.name || undefined,

                    status:
                        filters.status || undefined,

                    document:
                        filters.document || undefined,

                    per_page: 10,
                },
            }
        );

        customers.value =
            response.data.data;

        pagination.currentPage =
            response.data.meta.current_page;

        pagination.lastPage =
            response.data.meta.last_page;
    } catch (exception) {
        error.value =
            exception.response?.data?.message
            ?? 'Erro ao carregar clientes.';
    } finally {
        loading.value = false;
    }
}

async function changeStatus(customer) {
    error.value = '';
    message.value = '';

    try {
        const response = await api.patch(
            `/api/customers/${customer.id}/status`
        );

        customer.status =
            response.data.data.status;

        message.value =
            'Situação do cliente alterada com sucesso.';
    } catch (exception) {
        error.value =
            exception.response?.data?.message
            ?? 'Não foi possível alterar a situação.';
    }
}

function clearFilters() {
    filters.name = '';
    filters.status = '';
    filters.document = '';

    loadCustomers();
}

onMounted(() => {
    loadCustomers();
});
</script>

<template>
    <main class="container py-4">

        <div
            class="d-flex justify-content-between align-items-center mb-4"
        >
            <div>
                <h1 class="h3 mb-1">
                    Clientes
                </h1>

                <div class="text-muted">
                    Consulte e gerencie clientes.
                </div>
            </div>
        </div>

        <div
            v-if="message"
            class="alert alert-success"
        >
            {{ message }}
        </div>

        <div
            v-if="error"
            class="alert alert-danger"
        >
            {{ error }}
        </div>

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-body">

                <form
                    class="row g-3"
                    @submit.prevent="loadCustomers(1)"
                >

                    <div class="col-md-4">
                        <label class="form-label">
                            Nome
                        </label>

                        <input
                            v-model="filters.name"
                            class="form-control"
                            placeholder="Nome do cliente"
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">
                            CPF/CNPJ
                        </label>

                        <input
                            v-model="filters.document"
                            class="form-control"
                            placeholder="CPF ou CNPJ"
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">
                            Situação
                        </label>

                        <select
                            v-model="filters.status"
                            class="form-select"
                        >
                            <option value="">
                                Todas
                            </option>

                            <option value="ACTIVE">
                                Ativo
                            </option>

                            <option value="INACTIVE">
                                Inativo
                            </option>
                        </select>
                    </div>

                    <div
                        class="col-md-2 d-flex align-items-end gap-2"
                    >
                        <button
                            type="submit"
                            class="btn btn-dark"
                        >
                            Filtrar
                        </button>

                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            @click="clearFilters"
                        >
                            Limpar
                        </button>
                    </div>

                </form>

            </div>
        </div>

        <div class="card border-0 shadow-sm">

            <div class="table-responsive">

                <table
                    class="table table-hover align-middle mb-0"
                >
                    <thead class="table-light">
                        <tr>
                            <th>Cliente</th>
                            <th>CPF/CNPJ</th>
                            <th>Contato</th>
                            <th>Contratos</th>
                            <th>Situação</th>
                            <th class="text-end">
                                Ação
                            </th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr v-if="loading">
                            <td
                                colspan="6"
                                class="text-center py-5"
                            >
                                Carregando...
                            </td>
                        </tr>

                        <tr
                            v-else-if="
                                customers.length === 0
                            "
                        >
                            <td
                                colspan="6"
                                class="text-center text-muted py-5"
                            >
                                Nenhum cliente encontrado.
                            </td>
                        </tr>

                        <tr
                            v-for="customer in customers"
                            v-else
                            :key="customer.id"
                        >
                            <td>
                                <div class="fw-semibold">
                                    {{ customer.name }}
                                </div>

                                <small class="text-muted">
                                    {{ customer.address }}
                                </small>
                            </td>

                            <td>
                                {{
                                    formatDocument(
                                        customer.document
                                    )
                                }}
                            </td>

                            <td>
                                {{ customer.contact }}
                            </td>

                            <td>
                                {{
                                    customer.contracts_count
                                    ?? 0
                                }}
                            </td>

                            <td>
                                <span
                                    class="badge"
                                    :class="
                                        customer.status === 'ACTIVE'
                                            ? 'text-bg-success'
                                            : 'text-bg-secondary'
                                    "
                                >
                                    {{
                                        customer.status === 'ACTIVE'
                                            ? 'Ativo'
                                            : 'Inativo'
                                    }}
                                </span>
                            </td>

                            <td class="text-end">

                                <button
                                    class="btn btn-sm"
                                    :class="
                                        customer.status === 'ACTIVE'
                                            ? 'btn-outline-danger'
                                            : 'btn-outline-success'
                                    "
                                    @click="
                                        changeStatus(customer)
                                    "
                                >
                                    {{
                                        customer.status === 'ACTIVE'
                                            ? 'Desativar'
                                            : 'Ativar'
                                    }}
                                </button>

                            </td>
                        </tr>

                    </tbody>
                </table>

            </div>

            <div
                v-if="pagination.lastPage > 1"
                class="card-footer bg-white d-flex justify-content-between"
            >
                <button
                    class="btn btn-outline-secondary btn-sm"
                    :disabled="
                        pagination.currentPage <= 1
                    "
                    @click="
                        loadCustomers(
                            pagination.currentPage - 1
                        )
                    "
                >
                    Anterior
                </button>

                <span class="small text-muted">
                    Página
                    {{ pagination.currentPage }}
                    de
                    {{ pagination.lastPage }}
                </span>

                <button
                    class="btn btn-outline-secondary btn-sm"
                    :disabled="
                        pagination.currentPage
                        >= pagination.lastPage
                    "
                    @click="
                        loadCustomers(
                            pagination.currentPage + 1
                        )
                    "
                >
                    Próxima
                </button>
            </div>

        </div>

    </main>
</template>