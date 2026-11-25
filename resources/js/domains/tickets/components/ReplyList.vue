<script setup lang="ts">
import { ref } from 'vue';
import FormError from '@/components/FormError.vue';
import ReplyForm from './ReplyForm.vue';
import { fullName, formatDate } from '../helpers';
import { ticketStore, type TicketReply } from '../store';

const props = defineProps<{
    ticketId: number;
    replies: TicketReply[];
    isAdmin: boolean;
}>();

const editingReplyId = ref<number | null>(null);
const editingReplyBody = ref('');
const saving = ref(false);

const startEdit = (reply: TicketReply) => {
    editingReplyId.value = reply.id;
    editingReplyBody.value = reply.body;
};

const cancelEdit = () => {
    editingReplyId.value = null;
    editingReplyBody.value = '';
};

const saveReply = async () => {
    if (editingReplyId.value === null) return;

    saving.value = true;

    try {
        await ticketStore.actions.updateReply(editingReplyId.value, editingReplyBody.value);
        cancelEdit();
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        saving.value = false;
    }
};
</script>

<template>
    <section>
        <h2>Reacties</h2>

        <p v-if="!replies.length">Nog geen reacties.</p>

        <ul v-else>
            <li v-for="reply in replies" :key="reply.id">
                <p>{{ fullName(reply.author) }} - {{ formatDate(reply.created_at) }}</p>

                <template v-if="editingReplyId === reply.id">
                    <p>
                        <label :for="'reply-' + reply.id">Reactie</label>
                        <textarea :id="'reply-' + reply.id" v-model="editingReplyBody" rows="3"></textarea>
                    </p>
                    <FormError name="body" />

                    <button type="button" :disabled="saving" @click="saveReply">Opslaan</button>
                    <button type="button" :disabled="saving" @click="cancelEdit">Annuleren</button>
                </template>

                <template v-else>
                    <p>{{ reply.body }}</p>
                    <button v-if="isAdmin" type="button" @click="startEdit(reply)">Bewerk</button>
                </template>
            </li>
        </ul>

        <template v-if="isAdmin && editingReplyId === null">
            <h3>Reactie toevoegen</h3>
            <ReplyForm :ticket-id="ticketId" />
        </template>
    </section>
</template>
