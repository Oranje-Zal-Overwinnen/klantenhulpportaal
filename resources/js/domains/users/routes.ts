import Overview from './pages/Overview.vue';
import Edit from './pages/Edit.vue';

export const userRoutes = [
    {
        path: '/users',
        component: Overview,
        name: 'users.overview',
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/users/:id/edit',
        component: Edit,
        name: 'users.edit',
        meta: { requiresAuth: true, requiresAdmin: true }
    }
];
