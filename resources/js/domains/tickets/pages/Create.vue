<script setup lang="ts">
import { useRouter } from 'vue-router';
import Form from '../components/Form.vue';
import { ticketStore, type TicketFormData } from '@/domains/tickets/store';

const router = useRouter();

const handleSubmit = async (data: TicketFormData) => {
    try {
        await ticketStore.actions.createTicket(data);
        await router.push({ name: 'tickets.overview' });
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    }
};
</script>

<template>
    <main>
        <h1>Nieuw ticket</h1>

        <Form @submit="handleSubmit" />
    </main>
</template>
