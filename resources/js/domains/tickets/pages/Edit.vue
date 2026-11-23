<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ErrorMessage from '@/components/ErrorMessage.vue';
import Form from '../components/Form.vue';
import { ticketStore, type TicketFormData } from '@/domains/tickets/store';

const route = useRoute();
const router = useRouter();

const ticketId = Number(route.params.id);
const ticket = ticketStore.getters.getById(ticketId);
const ready = ref(false);

onMounted(async () => {
    try {
        await ticketStore.actions.fetchTicket(ticketId);
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        ready.value = true;
    }
});

const handleSubmit = async (data: TicketFormData) => {
    try {
        await ticketStore.actions.updateTicket(ticketId, data);
        await router.push({ name: 'tickets.show', params: { id: ticketId } });
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
