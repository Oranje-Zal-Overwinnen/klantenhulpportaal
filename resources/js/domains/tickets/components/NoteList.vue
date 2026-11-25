<script setup lang="ts">
import { ref } from 'vue';
import DeleteNoteButton from './DeleteNoteButton.vue';
import FormError from '@/components/FormError.vue';
import NoteForm from './NoteForm.vue';
import { fullName, formatDate } from '../helpers';
import { ticketStore, type TicketNote } from '../store';

const props = defineProps<{
    ticketId: number;
    notes: TicketNote[];
}>();

const editingNoteId = ref<number | null>(null);
const editingNoteBody = ref('');
const saving = ref(false);

const startEdit = (note: TicketNote) => {
    editingNoteId.value = note.id;
    editingNoteBody.value = note.body;
};

const cancelEdit = () => {
    editingNoteId.value = null;
    editingNoteBody.value = '';
};

const saveNote = async () => {
    if (editingNoteId.value === null) return;

    saving.value = true;

    try {
        await ticketStore.actions.updateNote(editingNoteId.value, editingNoteBody.value);
        cancelEdit();
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        saving.value = false;
    }
};

const handleDeleted = () => {
    cancelEdit();
};
</script>

<template>
    <section>
        <h2>Notities</h2>

        <p v-if="!notes.length">Nog geen notities.</p>

        <ul v-else class="mt-3 space-y-3">
            <li v-for="note in notes" :key="note.id" class="rounded-md border border-slate-200 bg-white p-3">
                <p>{{ fullName(note.author) }} - {{ formatDate(note.created_at) }}</p>

                <template v-if="editingNoteId === note.id">
                    <p>
                        <label :for="'note-' + note.id">Notitie</label>
                        <textarea :id="'note-' + note.id" v-model="editingNoteBody" rows="3"></textarea>
                    </p>
                    <FormError name="body" />

                    <button type="button" :disabled="saving" @click="saveNote">Opslaan</button>
                    <button type="button" :disabled="saving" @click="cancelEdit">Annuleren</button>
                </template>

                <template v-else>
                    <p>{{ note.body }}</p>
                    <button type="button" @click="startEdit(note)">Bewerk</button>
                    <DeleteNoteButton :note="note" @deleted="handleDeleted" />
                </template>
            </li>
        </ul>

        <template v-if="editingNoteId === null">
            <h3>Notitie toevoegen</h3>
            <NoteForm :ticket-id="ticketId" />
        </template>
    </section>
</template>
