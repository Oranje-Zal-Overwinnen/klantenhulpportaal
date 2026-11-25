<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import FormError from '@/components/FormError.vue';
import { userStore } from '@/domains/users/store';
import { ticketStore, type Ticket } from '@/domains/tickets/store';

const props = defineProps<{ ticket: Ticket }>();

const selectedAdmin = ref<number | null>(props.ticket.assigned_to);
const assigning = ref(false);

const admins = computed(() =>
    Object.values(userStore.getters.all.value)
        .filter(user => user.role === 'admin')
        .sort((first, second) =>
            `${first.last_name} ${first.first_name}`.localeCompare(`${second.last_name} ${second.first_name}`)
        )
);

watch(
    () => props.ticket.assigned_to,
    assignedTo => {
        selectedAdmin.value = assignedTo;
    }
);

onMounted(async () => {
    try {
        await userStore.actions.getAll();
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    }
});

const handleSubmit = async () => {
    assigning.value = true;

    try {
        await ticketStore.actions.assignTicket(props.ticket.id, selectedAdmin.value);
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        assigning.value = false;
    }
};
</script>

<template>
    <section>
        <h2>Toewijzen</h2>

        <form @submit.prevent="handleSubmit">
            <p>
                <label for="assigned_to">Toegewezen aan</label>
                <select id="assigned_to" v-model="selectedAdmin">
                    <option :value="null">Niet toegewezen</option>
                    <option v-for="admin in admins" :key="admin.id" :value="admin.id">
                        {{ admin.first_name }} {{ admin.last_name }}
                    </option>
                </select>
            </p>
            <FormError name="assigned_to" />

            <button type="submit" :disabled="assigning">Toewijzen</button>
        </form>
    </section>
</template>
