import { createRouter, createWebHistory } from 'vue-router';
import { userRoutes } from '@/domain/users/routes';
import { ticketRoutes } from '@/domain/tickets/routes';
import { noteRoutes } from '@/domain/notes/routes';

export const router = createRouter({
    history: createWebHistory(),
    routes: [...userRoutes, ...ticketRoutes, ...noteRoutes],
});
