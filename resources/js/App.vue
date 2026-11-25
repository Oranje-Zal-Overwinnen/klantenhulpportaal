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
    <header v-if="isAuthenticated && !route.meta.guestOnly" class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-6 py-3">
            <nav class="flex gap-4 text-sm font-medium">
                <RouterLink :to="{ name: 'tickets.overview' }" class="text-slate-600 hover:text-slate-900">Tickets</RouterLink>
                <template v-if="isAdmin">
                    <RouterLink :to="{ name: 'users.overview' }" class="text-slate-600 hover:text-slate-900">Gebruikers</RouterLink>
                    <RouterLink :to="{ name: 'categories.overview' }" class="text-slate-600 hover:text-slate-900">Categorieën</RouterLink>
                </template>
            </nav>

            <div v-if="user" class="flex items-center gap-3 text-sm text-slate-500">
                <span>Ingelogd als {{ user.first_name }} {{ user.last_name }}</span>
                <button
                    type="button"
                    class="border border-slate-300 bg-white text-slate-700 hover:bg-slate-100"
                    @click="handleLogout"
                >
                    Uitloggen
                </button>
            </div>
        </div>
    </header>

    <RouterView />
</template>
