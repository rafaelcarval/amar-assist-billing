<script setup>
import {
    onMounted,
    reactive,
    ref,
} from 'vue';

import api from '../lib/http';

import {
    useChargeRealtime,
} from '../composables/useChargeRealtime.js';

const charges = ref([]);
const loading = ref(false);
const error = ref('');
const message = ref('');

const pagination = reactive({
    currentPage: 1,
    lastPage: 1,
});

function formatMoney(value) {
    return new Intl.NumberFormat(
        'pt-BR',
        {
            style: 'currency',
            currency: 'BRL',
        }
    ).format(Number(value));
}

function formatDate(value) {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat(
        'pt-BR',
        {
            timeZone: 'UTC',
        }
    ).format(
        new Date(`${value}T00:00:00Z`)
    );
}

function paymentMethodLabel(method) {
    return {
        PIX: 'PIX',
        BOLETO: 'Boleto',
        CARD: 'Cartão',
    }[method] ?? method;
}

async function loadCharges(page = 1) {
    loading.value = true;
    error.value = '';

    try {
        const response = await api.get(
            '/api/charges',
            {
                params: {
                    page,
                },
            }
        );

        charges.value =
            response.data.data;

        pagination.currentPage =
            response.data.meta.current_page;

        pagination.lastPage =
            response.data.meta.last_page;
    } catch (exception) {
        error.value =
            exception.response?.data?.message
            ?? 'Erro ao carregar cobranças.';
    } finally {
        loading.value = false;
    }
}

async function markAsPaid(charge) {
    error.value = '';
    message.value = '';

    try {
        await api.patch(
            `/api/charges/${charge.id}/pay`
        );

        message.value =
            'Cobrança marcada como paga.';

        await loadCharges(
            pagination.currentPage
        );
    } catch (exception) {
        error.value =
            exception.response?.data?.message
            ?? 'Não foi possível atualizar a cobrança.';
    }
}

useChargeRealtime(() => {
    /*
     * Recarregamos do servidor para preservar
     * exatamente a regra de ordenação:
     *
     * 1. abertas vencidas
     * 2. abertas
     * 3. pagas
     */
    loadCharges(1);
});

onMounted(() => {
    loadCharges();
});
</script>

<template>
    <main class="container py-4">

        <div
            class="d-flex justify-content-between align-items-center mb-4"
        >
            <div>
                <h1 class="h3 mb-1">
                    Cobranças
                </h1>

                <div class="text-muted">
                    Abertas e vencidas aparecem primeiro.
                </div>
            </div>

            <span
                class="badge rounded-pill text-bg-success"
            >
                Realtime ativo
            </span>
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

        <div class="card border-0 shadow-sm">

            <div class="table-responsive">

                <table
                    class="table align-middle mb-0"
                >

                    <thead class="table-light">
                        <tr>
                            <th>Cliente</th>
                            <th>Vencimento</th>
                            <th>Pagamento</th>
                            <th>Valor</th>
                            <th>Multa</th>
                            <th>Total</th>
                            <th>Situação</th>
                            <th class="text-end">
                                Ação
                            </th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr v-if="loading">
                            <td
                                colspan="8"
                                class="text-center py-5"
                            >
                                Carregando...
                            </td>
                        </tr>

                        <tr
                            v-else-if="
                                charges.length === 0
                            "
                        >
                            <td
                                colspan="8"
                                class="text-center text-muted py-5"
                            >
                                Nenhuma cobrança encontrada.
                            </td>
                        </tr>

                        <tr
                            v-for="charge in charges"
                            v-else
                            :key="charge.id"
                            :class="{
                                'table-danger':
                                    charge.is_overdue,
                            }"
                        >

                            <td>
                                <div class="fw-semibold">
                                    {{
                                        charge.customer?.name
                                        ?? '-'
                                    }}
                                </div>

                                <small class="text-muted">
                                    Contrato
                                    #{{ charge.contract_id }}
                                </small>
                            </td>

                            <td>
                                {{
                                    formatDate(
                                        charge.due_date
                                    )
                                }}

                                <div
                                    v-if="
                                        charge.is_overdue
                                    "
                                    class="small text-danger fw-semibold"
                                >
                                    Vencida
                                </div>
                            </td>

                            <td>
                                {{
                                    paymentMethodLabel(
                                        charge.payment_method
                                    )
                                }}
                            </td>

                            <td>
                                {{
                                    formatMoney(
                                        charge.base_amount
                                    )
                                }}
                            </td>

                            <td>
                                {{
                                    formatMoney(
                                        charge.late_fee_amount
                                    )
                                }}
                            </td>

                            <td class="fw-semibold">
                                {{
                                    formatMoney(
                                        charge.total_amount
                                    )
                                }}
                            </td>

                            <td>
                                <span
                                    class="badge"
                                    :class="
                                        charge.status === 'PAID'
                                            ? 'text-bg-success'
                                            : charge.is_overdue
                                                ? 'text-bg-danger'
                                                : 'text-bg-warning'
                                    "
                                >
                                    {{
                                        charge.status === 'PAID'
                                            ? 'Paga'
                                            : charge.is_overdue
                                                ? 'Vencida'
                                                : 'Aberta'
                                    }}
                                </span>
                            </td>

                            <td class="text-end">

                                <button
                                    v-if="
                                        charge.status === 'OPEN'
                                    "
                                    class="btn btn-sm btn-outline-success"
                                    @click="
                                        markAsPaid(charge)
                                    "
                                >
                                    Marcar como paga
                                </button>

                                <span
                                    v-else
                                    class="text-muted small"
                                >
                                    Finalizada
                                </span>

                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

            <div
                v-if="pagination.lastPage > 1"
                class="card-footer bg-white d-flex justify-content-between align-items-center"
            >

                <button
                    class="btn btn-outline-secondary btn-sm"
                    :disabled="
                        pagination.currentPage <= 1
                    "
                    @click="
                        loadCharges(
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
                        loadCharges(
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