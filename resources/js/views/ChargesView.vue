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

const summary = ref({
    open: 0,
    overdue: 0,
    paid: 0,
    open_amount: '0.00',
});

const loading = ref(false);
const summaryLoading = ref(false);

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
    ).format(
        Number(value ?? 0)
    );
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


/**
 * Carrega o resumo das cobranças.
 *
 * O backend utiliza Redis através de
 * ChargeSummaryService + Cache::remember().
 */
async function loadSummary() {
    summaryLoading.value = true;

    try {
        const response = await api.get(
            '/api/charges/summary'
        );

        summary.value = response.data.data;
    } catch (exception) {
        console.error(
            'Erro ao carregar resumo das cobranças.',
            exception
        );
    } finally {
        summaryLoading.value = false;
    }
}


/**
 * Carrega a listagem paginada de cobranças.
 */
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


/**
 * Marca uma cobrança como paga.
 *
 * O backend invalida o cache do resumo.
 * Depois recarregamos tanto a listagem
 * quanto os indicadores.
 */
async function markAsPaid(charge) {
    error.value = '';
    message.value = '';

    try {
        await api.patch(
            `/api/charges/${charge.id}/pay`
        );

        message.value =
            'Cobrança marcada como paga.';

        await Promise.all([
            loadCharges(
                pagination.currentPage
            ),

            loadSummary(),
        ]);
    } catch (exception) {
        error.value =
            exception.response?.data?.message
            ?? 'Não foi possível atualizar a cobrança.';
    }
}


/**
 * Quando uma nova cobrança for recebida
 * via Laravel Echo, recarregamos a primeira
 * página para preservar a regra de ordenação:
 *
 * 1. abertas vencidas
 * 2. abertas
 * 3. pagas
 *
 * Também atualizamos o resumo.
 */
useChargeRealtime(() => {
    Promise.all([
        loadCharges(1),
        loadSummary(),
    ]);
});


/**
 * Primeira carga da tela.
 */
onMounted(() => {
    Promise.all([
        loadCharges(),
        loadSummary(),
    ]);
});
</script>


<template>
    <main class="container py-4">

        <!-- Cabeçalho -->
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


        <!-- Mensagens -->
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


        <!-- Resumo -->
        <div class="row g-3 mb-4">

            <!-- Em aberto -->
            <div class="col-sm-6 col-xl-3">
                <div
                    class="card border-0 shadow-sm h-100"
                >
                    <div class="card-body">

                        <div
                            class="text-muted text-uppercase small fw-semibold mb-2"
                        >
                            Em aberto
                        </div>

                        <div
                            v-if="summaryLoading"
                            class="placeholder-glow"
                        >
                            <span
                                class="placeholder col-4"
                            ></span>
                        </div>

                        <div
                            v-else
                            class="fs-2 fw-bold"
                        >
                            {{ summary.open }}
                        </div>

                    </div>
                </div>
            </div>


            <!-- Vencidas -->
            <div class="col-sm-6 col-xl-3">
                <div
                    class="card border-0 shadow-sm h-100"
                >
                    <div class="card-body">

                        <div
                            class="text-muted text-uppercase small fw-semibold mb-2"
                        >
                            Vencidas
                        </div>

                        <div
                            v-if="summaryLoading"
                            class="placeholder-glow"
                        >
                            <span
                                class="placeholder col-4"
                            ></span>
                        </div>

                        <div
                            v-else
                            class="fs-2 fw-bold text-danger"
                        >
                            {{ summary.overdue }}
                        </div>

                    </div>
                </div>
            </div>


            <!-- Pagas -->
            <div class="col-sm-6 col-xl-3">
                <div
                    class="card border-0 shadow-sm h-100"
                >
                    <div class="card-body">

                        <div
                            class="text-muted text-uppercase small fw-semibold mb-2"
                        >
                            Pagas
                        </div>

                        <div
                            v-if="summaryLoading"
                            class="placeholder-glow"
                        >
                            <span
                                class="placeholder col-4"
                            ></span>
                        </div>

                        <div
                            v-else
                            class="fs-2 fw-bold text-success"
                        >
                            {{ summary.paid }}
                        </div>

                    </div>
                </div>
            </div>


            <!-- Valor em aberto -->
            <div class="col-sm-6 col-xl-3">
                <div
                    class="card border-0 shadow-sm h-100"
                >
                    <div class="card-body">

                        <div
                            class="text-muted text-uppercase small fw-semibold mb-2"
                        >
                            Valor em aberto
                        </div>

                        <div
                            v-if="summaryLoading"
                            class="placeholder-glow"
                        >
                            <span
                                class="placeholder col-8"
                            ></span>
                        </div>

                        <div
                            v-else
                            class="fs-4 fw-bold"
                        >
                            {{
                                formatMoney(
                                    summary.open_amount
                                )
                            }}
                        </div>

                    </div>
                </div>
            </div>

        </div>


        <!-- Tabela de cobranças -->
        <div class="card border-0 shadow-sm">

            <div
                class="card-header bg-white border-0 py-3"
            >
                <div
                    class="d-flex justify-content-between align-items-center"
                >
                    <div>
                        <div class="fw-semibold">
                            Faturas
                        </div>

                        <div
                            class="text-muted small"
                        >
                            Cobranças abertas e vencidas
                            possuem prioridade.
                        </div>
                    </div>

                    <button
                        type="button"
                        class="btn btn-outline-secondary btn-sm"
                        :disabled="loading"
                        @click="
                            Promise.all([
                                loadCharges(
                                    pagination.currentPage
                                ),
                                loadSummary(),
                            ])
                        "
                    >
                        Atualizar
                    </button>
                </div>
            </div>


            <div class="table-responsive">

                <table
                    class="table table-hover align-middle mb-0"
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

                        <!-- Loading -->
                        <tr v-if="loading">
                            <td
                                colspan="8"
                                class="text-center py-5"
                            >
                                <div
                                    class="spinner-border spinner-border-sm me-2"
                                    role="status"
                                ></div>

                                Carregando cobranças...
                            </td>
                        </tr>


                        <!-- Sem registros -->
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


                        <!-- Registros -->
                        <template v-else>

                            <tr
                                v-for="charge in charges"
                                :key="charge.id"
                                :class="{
                                    'table-danger':
                                        charge.is_overdue,
                                }"
                            >

                                <!-- Cliente -->
                                <td>
                                    <div
                                        class="fw-semibold"
                                    >
                                        {{
                                            charge
                                                .customer
                                                ?.name
                                            ?? '-'
                                        }}
                                    </div>

                                    <small
                                        class="text-muted"
                                    >
                                        Contrato
                                        #{{
                                            charge.contract_id
                                        }}
                                    </small>
                                </td>


                                <!-- Vencimento -->
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


                                <!-- Método -->
                                <td>
                                    {{
                                        paymentMethodLabel(
                                            charge
                                                .payment_method
                                        )
                                    }}
                                </td>


                                <!-- Valor base -->
                                <td>
                                    {{
                                        formatMoney(
                                            charge
                                                .base_amount
                                        )
                                    }}
                                </td>


                                <!-- Multa -->
                                <td>
                                    {{
                                        formatMoney(
                                            charge
                                                .late_fee_amount
                                        )
                                    }}
                                </td>


                                <!-- Total -->
                                <td class="fw-semibold">
                                    {{
                                        formatMoney(
                                            charge
                                                .total_amount
                                        )
                                    }}
                                </td>


                                <!-- Situação -->
                                <td>
                                    <span
                                        class="badge"
                                        :class="
                                            charge.status
                                                === 'PAID'
                                                ? 'text-bg-success'
                                                : charge.is_overdue
                                                    ? 'text-bg-danger'
                                                    : 'text-bg-warning'
                                        "
                                    >
                                        {{
                                            charge.status
                                                === 'PAID'
                                                ? 'Paga'
                                                : charge.is_overdue
                                                    ? 'Vencida'
                                                    : 'Aberta'
                                        }}
                                    </span>
                                </td>


                                <!-- Ações -->
                                <td class="text-end">

                                    <button
                                        v-if="
                                            charge.status
                                                === 'OPEN'
                                        "
                                        type="button"
                                        class="btn btn-sm btn-outline-success"
                                        @click="
                                            markAsPaid(
                                                charge
                                            )
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

                        </template>

                    </tbody>

                </table>

            </div>


            <!-- Paginação -->
            <div
                v-if="pagination.lastPage > 1"
                class="card-footer bg-white d-flex justify-content-between align-items-center"
            >

                <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm"
                    :disabled="
                        pagination.currentPage <= 1
                        || loading
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
                    type="button"
                    class="btn btn-outline-secondary btn-sm"
                    :disabled="
                        pagination.currentPage
                            >= pagination.lastPage
                        || loading
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