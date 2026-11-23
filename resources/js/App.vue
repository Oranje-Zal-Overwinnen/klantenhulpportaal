<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router';
import { authStore } from '@/domains/auth/store';

const route = useRoute();
const router = useRouter();

const user = authStore.getters.user;
const isAuthenticated = authStore.getters.isAuthenticated;
const isAdmin = authStore.getters.isAdmin;

const handleLogout = async () => {
    await authStore.actions.logout();
    await router.push({ name: 'auth.login' });
};
</script>

<template>
    <header v-if="isAuthenticated && route.name !== 'auth.login'">
        <nav>
            <RouterLink :to="{ name: 'tickets.overview' }">Tickets</RouterLink>
            <template v-if="isAdmin">
                <span> | </span>
                <RouterLink :to="{ name: 'users.overview' }">Gebruikers</RouterLink>
            </template>
        </nav>

        <p v-if="user">
            Ingelogd als {{ user.first_name }} {{ user.last_name }} ({{ user.role }})
            <button type="button" @click="handleLogout">Uitloggen</button>
        </p>
    </header>

    <RouterView />
</template>
