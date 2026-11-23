<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ErrorMessage from '@/components/ErrorMessage.vue';
import FormError from '@/components/FormError.vue';
import { authStore } from '@/domains/auth/store';
import type { Note } from '@/domains/notes/store';
import type { Reply } from '@/domains/replies/store';
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
const replyBody = ref('');
const submittingReply = ref(false);
const editingReplyId = ref<number | null>(null);
const editingReplyBody = ref('');
const savingReply = ref(false);
const noteBody = ref('');
const submittingNote = ref(false);
const editingNoteId = ref<number | null>(null);
const editingNoteBody = ref('');
const savingNote = ref(false);
const pendingDeleteNote = ref<Readonly<Note> | null>(null);
const deletingNote = ref(false);

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

const addReply = async () => {
    if (!ticket.value) return;

    submittingReply.value = true;

    try {
        await ticketStore.actions.createReply(ticket.value.id, replyBody.value);
        replyBody.value = '';
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        submittingReply.value = false;
    }
};

const startEditReply = (reply: Reply) => {
    editingReplyId.value = reply.id;
    editingReplyBody.value = reply.body;
};

const cancelEditReply = () => {
    editingReplyId.value = null;
    editingReplyBody.value = '';
};

const saveReply = async () => {
    if (editingReplyId.value === null) return;

    savingReply.value = true;

    try {
        await ticketStore.actions.updateReply(editingReplyId.value, editingReplyBody.value);
        cancelEditReply();
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        savingReply.value = false;
    }
};

const addNote = async () => {
    if (!ticket.value) return;

    submittingNote.value = true;

    try {
        await ticketStore.actions.createNote(ticket.value.id, noteBody.value);
        noteBody.value = '';
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        submittingNote.value = false;
    }
};

const startEditNote = (note: Note) => {
    editingNoteId.value = note.id;
    editingNoteBody.value = note.body;
};

const cancelEditNote = () => {
    editingNoteId.value = null;
    editingNoteBody.value = '';
};

const saveNote = async () => {
    if (editingNoteId.value === null) return;

    savingNote.value = true;

    try {
        await ticketStore.actions.updateNote(editingNoteId.value, editingNoteBody.value);
        cancelEditNote();
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        savingNote.value = false;
    }
};

const requestDeleteNote = (note: Readonly<Note>) => {
    pendingDeleteNote.value = note;
};

const cancelDeleteNote = () => {
    pendingDeleteNote.value = null;
};

const deleteNote = async () => {
    if (!pendingDeleteNote.value) return;

    deletingNote.value = true;

    try {
        await ticketStore.actions.deleteNote(pendingDeleteNote.value.id);
        pendingDeleteNote.value = null;
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        deletingNote.value = false;
    }
};
</script>

<template>
    <main>
        <p>
            <RouterLink :to="{ name: 'tickets.overview' }">Terug naar overzicht</RouterLink>
        </p>

        <ErrorMessage v-if="pendingDeleteNote === null" />

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

                    <template v-if="editingReplyId === reply.id">
                        <p>
                            <label :for="'reply-' + reply.id">Reactie</label>
                            <textarea :id="'reply-' + reply.id" v-model="editingReplyBody" rows="3"></textarea>
                        </p>
                        <FormError name="body" />

                        <button type="button" :disabled="savingReply" @click="saveReply">Opslaan</button>
                        <button type="button" :disabled="savingReply" @click="cancelEditReply">Annuleren</button>
                    </template>

                    <template v-else>
                        <p>{{ reply.body }}</p>
                        <button v-if="isAdmin" type="button" @click="startEditReply(reply)">Bewerk</button>
                    </template>
                </li>
            </ul>

            <section v-if="isAdmin && editingReplyId === null">
                <h3>Reactie toevoegen</h3>

                <form @submit.prevent="addReply">
                    <p>
                        <label for="reply_body">Reactie</label>
                        <textarea id="reply_body" v-model="replyBody" rows="3" required></textarea>
                    </p>
                    <FormError name="body" />

                    <button type="submit" :disabled="submittingReply">Reactie plaatsen</button>
                </form>
            </section>

            <section v-if="isAdmin">
                <h2>Notities</h2>

                <p v-if="!ticket.notes?.length">Nog geen notities.</p>

                <ul v-else>
                    <li v-for="note in ticket.notes" :key="note.id">
                        <p>{{ fullName(note.author) }} - {{ formatDate(note.created_at) }}</p>

                        <template v-if="editingNoteId === note.id">
                            <p>
                                <label :for="'note-' + note.id">Notitie</label>
                                <textarea :id="'note-' + note.id" v-model="editingNoteBody" rows="3"></textarea>
                            </p>
                            <FormError name="body" />

                            <button type="button" :disabled="savingNote" @click="saveNote">Opslaan</button>
                            <button type="button" :disabled="savingNote" @click="cancelEditNote">Annuleren</button>
                        </template>

                        <template v-else>
                            <p>{{ note.body }}</p>
                            <button type="button" @click="startEditNote(note)">Bewerk</button>
                            <button type="button" @click="requestDeleteNote(note)">Verwijder</button>
                        </template>
                    </li>
                </ul>

                <form v-if="editingNoteId === null" @submit.prevent="addNote">
                    <p>
                        <label for="note_body">Notitie</label>
                        <textarea id="note_body" v-model="noteBody" rows="3" required></textarea>
                    </p>
                    <FormError name="body" />

                    <button type="submit" :disabled="submittingNote">Notitie toevoegen</button>
                </form>

                <ConfirmDialog
                    :open="pendingDeleteNote !== null"
                    :busy="deletingNote"
                    title="Notitie verwijderen"
                    confirm-label="Verwijderen"
                    @cancel="cancelDeleteNote"
                    @confirm="deleteNote"
                >
                    <p v-if="pendingDeleteNote">Weet je zeker dat je deze notitie wilt verwijderen?</p>

                    <ErrorMessage />
                </ConfirmDialog>
            </section>
        </template>

        <p v-else-if="loaded">Ticket niet gevonden of je hebt geen toegang.</p>
    </main>
</template>
