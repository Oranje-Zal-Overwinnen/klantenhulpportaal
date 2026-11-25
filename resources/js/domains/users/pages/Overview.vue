<script setup lang="ts">
import { computed, onMounted } from 'vue';
import DeleteButton from '../components/DeleteButton.vue';
import ErrorMessage from '@/components/ErrorMessage.vue';
import { userStore } from '@/domains/users/store';

const users = computed(() =>
    Object.values(userStore.getters.all.value).sort((first, second) =>
        `${first.last_name} ${first.first_name}`.localeCompare(`${second.last_name} ${second.first_name}`)
    )
);

onMounted(async () => {
    try {
        await userStore.actions.getAll();
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    }
});
</script>

<template>
    <main>
        <h1>Gebruikers</h1>

        <ErrorMessage />

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
                        <DeleteButton :user="user" />
                    </td>
                </tr>
            </tbody>
        </table>
    </main>
</template>
