<script setup>
import { ref } from 'vue';

import {
    useChargeRealtime,
} from '../composables/useChargeRealtime';


const realtimeCharges = ref([]);


useChargeRealtime((charge) => {
    realtimeCharges.value.unshift(
        charge
    );
});
</script>


<template>
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-5">

                        <h1 class="mb-3">
                            Amar Assist Billing
                        </h1>

                        <p class="lead">
                            Sistema de cobranças
                        </p>

                        <div
                            class="alert alert-success"
                        >
                            Laravel Echo conectado.
                        </div>

                    </div>
                </div>


                <div class="card shadow-sm">

                    <div class="card-header">
                        Cobranças recebidas em realtime
                    </div>

                    <div class="card-body">

                        <div
                            v-if="
                                realtimeCharges.length === 0
                            "
                            class="text-muted"
                        >
                            Aguardando evento...
                        </div>


                        <div
                            v-for="charge in realtimeCharges"
                            :key="charge.id"
                            class="border rounded p-3 mb-2"
                        >

                            <strong>
                                {{ charge.customer.name }}
                            </strong>

                            <div>
                                Valor:
                                R$ {{ charge.total_amount }}
                            </div>

                            <div>
                                Vencimento:
                                {{ charge.due_date }}
                            </div>

                            <div>
                                Tipo:
                                {{ charge.payment_method }}
                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </div>
    </main>
</template>