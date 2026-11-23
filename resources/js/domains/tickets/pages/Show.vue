<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import ErrorMessage from '@/components/ErrorMessage.vue';
import { authStore } from '@/domains/auth/store';
import { categoryNames, formatDate, fullName, statusLabel } from '@/domains/tickets/helpers';
import { ticketStore, type Ticket } from '@/domains/tickets/store';
import { userStore } from '@/domains/users/store';

const route = useRoute();
const ticketId = Number(route.params.id);
const ticket = ticketStore.getters.getById(ticketId);
const currentUser = authStore.getters.user;
const loaded = ref(false);
const selectedAdmin = ref<number | null>(null);
const assigning = ref(false);
const selectedStatus = ref<Ticket['status']>('pending');
const updatingStatus = ref(false);
const statuses: Ticket['status'][] = ['pending', 'in_progress', 'resolved'];

const canEdit = computed(() => {
    if (!ticket.value || !currentUser.value) return false;

    return currentUser.value.role === 'admin' || ticket.value.user_id === currentUser.value.id;
});

const isAdmin = computed(() => currentUser.value?.role === 'admin');

const admins = computed(() =>
    Object.values(userStore.getters.all.value)
        .filter(user => user.role === 'admin')
        .sort((first, second) =>
            `${first.last_name} ${first.first_name}`.localeCompare(`${second.last_name} ${second.first_name}`)
        )
);

onMounted(async () => {
    try {
        await ticketStore.actions.fetchTicket(ticketId);
        selectedAdmin.value = ticket.value?.assigned_to ?? null;
        selectedStatus.value = ticket.value?.status ?? 'pending';

        if (isAdmin.value) {
            await userStore.actions.getAll();
        }
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        loaded.value = true;
    }
});

const assign = async () => {
    if (!ticket.value) return;

    assigning.value = true;

    try {
        await ticketStore.actions.assignTicket(ticket.value.id, selectedAdmin.value);
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        assigning.value = false;
    }
};

const updateStatus = async () => {
    if (!ticket.value) return;

    updatingStatus.value = true;

    try {
        await ticketStore.actions.updateStatus(ticket.value.id, selectedStatus.value);
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        updatingStatus.value = false;
    }
};
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

            <section v-if="isAdmin">
                <h2>Toewijzen</h2>

                <p>
                    <label for="assigned_to">Toegewezen aan</label>
                    <select id="assigned_to" v-model="selectedAdmin">
                        <option :value="null">Niet toegewezen</option>
                        <option v-for="admin in admins" :key="admin.id" :value="admin.id">
                            {{ admin.first_name }} {{ admin.last_name }}
                        </option>
                    </select>
                </p>

                <button type="button" :disabled="assigning" @click="assign">Toewijzen</button>
            </section>

            <section v-if="isAdmin">
                <h2>Status wijzigen</h2>

                <p>
                    <label for="status">Status</label>
                    <select id="status" v-model="selectedStatus">
                        <option v-for="status in statuses" :key="status" :value="status">
                            {{ statusLabel(status) }}
                        </option>
                    </select>
                </p>

                <button type="button" :disabled="updatingStatus" @click="updateStatus">Status opslaan</button>
            </section>

            <h2>Reacties</h2>

            <p v-if="!ticket.replies?.length">Nog geen reacties.</p>

            <ul v-else>
                <li v-for="reply in ticket.replies" :key="reply.id">
                    <p>{{ fullName(reply.author) }} - {{ formatDate(reply.created_at) }}</p>
                    <p>{{ reply.body }}</p>
                </li>
            </ul>
        </template>

        <p v-else-if="loaded">Ticket niet gevonden of je hebt geen toegang.</p>
    </main>
</template>
