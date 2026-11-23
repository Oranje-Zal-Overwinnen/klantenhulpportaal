import Login from './pages/Login.vue';
import Register from './pages/Register.vue';

export const authRoutes = [
    { path: '/login', component: Login, name: 'auth.login', meta: { guestOnly: true } },
    { path: '/register', component: Register, name: 'auth.register', meta: { guestOnly: true } }
];
