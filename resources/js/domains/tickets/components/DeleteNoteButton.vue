<script setup lang="ts">
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ErrorMessage from '@/components/ErrorMessage.vue';
import { ticketStore, type TicketNote } from '@/domains/tickets/store';

const props = defineProps<{ note: TicketNote }>();
const emit = defineEmits<{ deleted: [] }>();

const open = ref(false);
const deleting = ref(false);

const close = () => {
    if (deleting.value) return;
    open.value = false;
};

const deleteNote = async () => {
    deleting.value = true;
    const deleted = await ticketStore.actions.deleteNote(props.note.id);
    deleting.value = false;

    if (deleted) {
        open.value = false;
        emit('deleted');
    }
};
</script>

<template>
    <button type="button" class="bg-red-600 hover:bg-red-700" @click="open = true">Verwijder</button>

    <ConfirmDialog
        :open="open"
        :busy="deleting"
        title="Notitie verwijderen"
        confirm-label="Verwijderen"
        @cancel="close"
        @confirm="deleteNote"
    >
        <p>Weet je zeker dat je deze notitie wilt verwijderen?</p>

        <ErrorMessage />
    </ConfirmDialog>
</template>
