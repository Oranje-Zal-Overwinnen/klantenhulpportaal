<script setup lang="ts">
import { ref, watch } from 'vue';
import { TICKET_STATUSES, ticketStore, type Ticket, type TicketStatus } from '@/domains/tickets/store';
import { statusLabel } from '../helpers';

const props = defineProps<{ ticket: Ticket }>();

const selectedStatus = ref<TicketStatus>(props.ticket.status);
const saving = ref(false);

watch(
    () => props.ticket.status,
    status => {
        selectedStatus.value = status;
    }
);

const handleSubmit = async () => {
    saving.value = true;

    try {
        await ticketStore.actions.updateStatus(props.ticket.id, selectedStatus.value);
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        saving.value = false;
    }
};
</script>

<template>
    <section>
        <h2>Status wijzigen</h2>

        <form @submit.prevent="handleSubmit">
            <p>
                <label for="status">Status</label>
                <select id="status" v-model="selectedStatus">
                    <option v-for="status in TICKET_STATUSES" :key="status" :value="status">
                        {{ statusLabel(status) }}
                    </option>
                </select>
            </p>

            <button type="submit" :disabled="saving">Status opslaan</button>
        </form>
    </section>
</template>
