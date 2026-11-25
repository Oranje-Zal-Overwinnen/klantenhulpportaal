<script setup lang="ts">
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ErrorMessage from '@/components/ErrorMessage.vue';
import { userStore, type User } from '@/domains/users/store';

const props = defineProps<{ user: Readonly<User> }>();
const emit = defineEmits<{ deleted: [] }>();

const open = ref(false);
const deleting = ref(false);

const close = () => {
    if (deleting.value) return;
    open.value = false;
};

const deleteUser = async () => {
    deleting.value = true;
    const deleted = await userStore.actions.delete(props.user.id);
    deleting.value = false;

    if (deleted) {
        open.value = false;
        emit('deleted');
    }
};
</script>

<template>
    <button type="button" @click="open = true">Verwijder</button>

    <ConfirmDialog
        :open="open"
        :busy="deleting"
        title="Gebruiker verwijderen"
        confirm-label="Verwijderen"
        @cancel="close"
        @confirm="deleteUser"
    >
        <p>Weet je zeker dat je {{ user.first_name }} {{ user.last_name }} wilt verwijderen?</p>

        <ErrorMessage />
    </ConfirmDialog>
</template>
