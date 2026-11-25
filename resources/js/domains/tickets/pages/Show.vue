<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import ErrorMessage from '@/components/ErrorMessage.vue';
import { authStore } from '@/domains/auth/store';
import AssignmentForm from '../components/AssignmentForm.vue';
import NoteList from '../components/NoteList.vue';
import ReplyList from '../components/ReplyList.vue';
import StatusForm from '../components/StatusForm.vue';
import { categoryNames, formatDate, fullName, statusLabel } from '../helpers';
import { ticketStore } from '../store';

const route = useRoute();

const currentUser = authStore.getters.user;
const ticketId = computed(() => Number(route.params.id));
const ticket = computed(() => ticketStore.getters.getById(ticketId.value).value);
const loaded = ref(false);

const isAdmin = computed(() => currentUser.value?.role === 'admin');

const canEdit = computed(() => {
    if (!ticket.value || !currentUser.value) return false;

    return isAdmin.value || ticket.value.user_id === currentUser.value.id;
});

const loadTicket = async () => {
    loaded.value = false;

    try {
        await ticketStore.actions.fetchTicket(ticketId.value);
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        loaded.value = true;
    }
};

watch(ticketId, loadTicket, { immediate: true });
</script>

<template>
    <main>
        <p>
            <RouterLink :to="{ name: 'tickets.overview' }">Terug naar overzicht</RouterLink>
        </p>

        <ErrorMessage />

        <template v-if="ticket">
            <h1>{{ ticket.title }}</h1>

            <p v-if="canEdit">
                <RouterLink :to="{ name: 'tickets.edit', params: { id: ticket.id } }">Bewerk</RouterLink>
            </p>

            <dl>
                <dt>Omschrijving</dt>
                <dd>{{ ticket.description }}</dd>

                <dt>Status</dt>
                <dd>{{ statusLabel(ticket.status) }}</dd>

                <dt>Categorieën</dt>
                <dd>{{ categoryNames(ticket) }}</dd>

                <dt>Aangemaakt door</dt>
                <dd>{{ fullName(ticket.creator) }}</dd>

                <dt>Aangemaakt op</dt>
                <dd>{{ formatDate(ticket.created_at) }}</dd>

                <dt>Laatste update op</dt>
                <dd>{{ formatDate(ticket.updated_at) }}</dd>

                <dt>Toegewezen aan</dt>
                <dd>{{ fullName(ticket.assignee) }}</dd>
            </dl>

            <AssignmentForm v-if="isAdmin" :ticket="ticket" />

            <StatusForm v-if="isAdmin" :ticket="ticket" />

            <ReplyList :ticket-id="ticket.id" :replies="ticket.replies ?? []" :is-admin="isAdmin" />

            <NoteList v-if="isAdmin" :ticket-id="ticket.id" :notes="ticket.notes ?? []" />
        </template>

        <p v-else-if="loaded">Ticket niet gevonden of je hebt geen toegang.</p>
    </main>
</template>
