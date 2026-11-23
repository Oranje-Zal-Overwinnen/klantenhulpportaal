import Overview from './pages/Overview.vue';
import Create from './pages/Create.vue';
import Edit from './pages/Edit.vue';

export const categoryRoutes = [
    {
        path: '/categories',
        component: Overview,
        name: 'categories.overview',
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/categories/create',
        component: Create,
        name: 'categories.create',
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/categories/:id/edit',
        component: Edit,
        name: 'categories.edit',
        meta: { requiresAuth: true, requiresAdmin: true }
    }
];
