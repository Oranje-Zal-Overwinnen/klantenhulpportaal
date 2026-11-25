<script setup lang="ts">
import { ref } from 'vue';
import FormError from '@/components/FormError.vue';
import { ticketStore } from '@/domains/tickets/store';

const props = defineProps<{ ticketId: number }>();

const body = ref('');
const submitting = ref(false);

const handleSubmit = async () => {
    submitting.value = true;

    try {
        await ticketStore.actions.createReply(props.ticketId, body.value);
        body.value = '';
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <form @submit.prevent="handleSubmit">
        <p>
            <label for="reply_body">Reactie</label>
            <textarea id="reply_body" v-model="body" rows="3" required></textarea>
        </p>
        <FormError name="body" />

        <button type="submit" :disabled="submitting">Reactie plaatsen</button>
    </form>
</template>
