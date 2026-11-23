<script setup lang="ts">
import { computed, onMounted } from 'vue';
import ErrorMessage from '@/components/ErrorMessage.vue';
import { categoryNames, formatDate, fullName, statusLabel } from '@/domains/tickets/helpers';
import { ticketStore } from '@/domains/tickets/store';

const tickets = computed(() =>
    Object.values(ticketStore.getters.all.value).sort((first, second) => {
        const firstDate = first.created_at ? new Date(first.created_at).getTime() : 0;
        const secondDate = second.created_at ? new Date(second.created_at).getTime() : 0;

        return secondDate - firstDate || second.id - first.id;
    })
);

onMounted(async () => {
    try {
        await ticketStore.actions.getAll();
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    }
});
</script>

<template>
    <main>
        <h1>Ticketoverzicht</h1>

        <p>
            <RouterLink :to="{ name: 'tickets.create' }">Nieuw ticket</RouterLink>
        </p>

        <ErrorMessage />

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titel</th>
                    <th>Categorieën</th>
                    <th>Status</th>
                    <th>Aangemaakt door</th>
                    <th>Aangemaakt op</th>
                    <th>Laatste update op</th>
                    <th>Toegewezen aan</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="ticket in tickets" :key="ticket.id">
                    <td>{{ ticket.id }}</td>
                    <td>
                        <RouterLink :to="{ name: 'tickets.show', params: { id: ticket.id } }">
                            {{ ticket.title }}
                        </RouterLink>
                    </td>
                    <td>{{ categoryNames(ticket) }}</td>
                    <td>{{ statusLabel(ticket.status) }}</td>
                    <td>{{ fullName(ticket.creator) }}</td>
                    <td>{{ formatDate(ticket.created_at) }}</td>
                    <td>{{ formatDate(ticket.updated_at) }}</td>
                    <td>{{ fullName(ticket.assignee) }}</td>
                </tr>
            </tbody>
        </table>

        <p v-if="tickets.length === 0">Geen tickets gevonden.</p>
    </main>
</template>
