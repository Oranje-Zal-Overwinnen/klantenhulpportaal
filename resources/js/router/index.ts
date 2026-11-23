import { createRouter, createWebHistory } from 'vue-router';
import { authRoutes } from '@/domains/auth/routes';
import { authStore } from '@/domains/auth/store';
import { noteRoutes } from '@/domains/notes/routes';
import { ticketRoutes } from '@/domains/tickets/routes';
import { userRoutes } from '@/domains/users/routes';

export const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', redirect: { name: 'tickets.overview' } },
        ...authRoutes,
        ...ticketRoutes,
        ...userRoutes,
        ...noteRoutes,
        { path: '/:pathMatch(.*)*', redirect: { name: 'tickets.overview' } }
    ]
});

router.beforeEach(async to => {
    if ((to.meta.requiresAuth || to.meta.requiresAdmin) && !authStore.getters.isAuthenticated.value) {
        await authStore.actions.fetchUser();
    }

    if (to.meta.requiresAuth && !authStore.getters.isAuthenticated.value) {
        return { name: 'auth.login', query: { redirect: to.fullPath } };
    }

    if (to.meta.requiresAdmin && !authStore.getters.isAdmin.value) {
        return { name: 'tickets.overview' };
    }

    if ((to.name === 'auth.login' || to.name === 'auth.register') && authStore.getters.isAuthenticated.value) {
        return { name: 'tickets.overview' };
    }

    return true;
});
