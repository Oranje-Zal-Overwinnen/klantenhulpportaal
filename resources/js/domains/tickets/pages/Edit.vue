<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ErrorMessage from '@/components/ErrorMessage.vue';
import Form from '../components/Form.vue';
import { ticketStore, type TicketFormData } from '@/domains/tickets/store';

const route = useRoute();
const router = useRouter();

const ticketId = computed(() => Number(route.params.id));
const ticket = computed(() => ticketStore.getters.getById(ticketId.value).value);
const ready = ref(false);

const loadTicket = async () => {
    ready.value = false;

    try {
        await ticketStore.actions.fetchTicket(ticketId.value);
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        ready.value = true;
    }
};

watch(ticketId, loadTicket, { immediate: true });

const handleSubmit = async (data: TicketFormData) => {
    try {
        await ticketStore.actions.updateTicket(ticketId.value, data);
        await router.push({ name: 'tickets.show', params: { id: ticketId.value } });
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    }
};
</script>

<template>
    <main>
        <h1>Ticket bewerken</h1>

        <p>
            <RouterLink :to="{ name: 'tickets.show', params: { id: ticketId } }">Terug naar ticket</RouterLink>
        </p>

        <ErrorMessage />

        <Form v-if="ready && ticket" :ticket="ticket" @submit="handleSubmit" />

        <p v-else-if="ready">Ticket niet gevonden of je hebt geen toegang.</p>
    </main>
</template>
