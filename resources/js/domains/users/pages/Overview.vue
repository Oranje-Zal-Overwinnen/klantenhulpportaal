<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ErrorMessage from '@/components/ErrorMessage.vue';
import { userStore, type User } from '@/domains/users/store';

const users = computed(() =>
    Object.values(userStore.getters.all.value).sort((first, second) =>
        `${first.last_name} ${first.first_name}`.localeCompare(`${second.last_name} ${second.first_name}`)
    )
);

const pendingDelete = ref<Readonly<User> | null>(null);
const deleting = ref(false);

onMounted(async () => {
    try {
        await userStore.actions.getAll();
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    }
});

const requestDelete = (user: Readonly<User>) => {
    pendingDelete.value = user;
};

const cancelDelete = () => {
    pendingDelete.value = null;
};

const deleteUser = async () => {
    if (!pendingDelete.value) return;

    deleting.value = true;
    const deleted = await userStore.actions.delete(pendingDelete.value.id);
    deleting.value = false;

    if (deleted) {
        pendingDelete.value = null;
    }
};
</script>

<template>
    <main>
        <h1>Gebruikers</h1>

        <ErrorMessage v-if="pendingDelete === null" />

        <table>
            <thead>
                <tr>
                    <th>Voornaam</th>
                    <th>Achternaam</th>
                    <th>E-mailadres</th>
                    <th>Rol</th>
                    <th>Telefoonnummer</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in users" :key="user.id">
                    <td>{{ user.first_name }}</td>
                    <td>{{ user.last_name }}</td>
                    <td>{{ user.email }}</td>
                    <td>{{ user.role }}</td>
                    <td>{{ user.phone_number ?? '-' }}</td>
                    <td>
                        <RouterLink :to="{ name: 'users.edit', params: { id: user.id } }">Bewerk</RouterLink>
                        <button type="button" @click="requestDelete(user)">Verwijder</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <ConfirmDialog
            :open="pendingDelete !== null"
            :busy="deleting"
            title="Gebruiker verwijderen"
            confirm-label="Verwijderen"
            @cancel="cancelDelete"
            @confirm="deleteUser"
        >
            <p v-if="pendingDelete">
                Weet je zeker dat je {{ pendingDelete.first_name }} {{ pendingDelete.last_name }} wilt verwijderen?
            </p>

            <ErrorMessage />
        </ConfirmDialog>
    </main>
</template>
