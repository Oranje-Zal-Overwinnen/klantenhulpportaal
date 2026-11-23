import Login from './pages/Login.vue';
import Register from './pages/Register.vue';
import ForgotPassword from './pages/ForgotPassword.vue';
import ResetPassword from './pages/ResetPassword.vue';

export const authRoutes = [
    { path: '/login', component: Login, name: 'auth.login', meta: { guestOnly: true } },
    { path: '/register', component: Register, name: 'auth.register', meta: { guestOnly: true } },
    { path: '/forgot-password', component: ForgotPassword, name: 'auth.forgot-password', meta: { guestOnly: true } },
    { path: '/reset-password', component: ResetPassword, name: 'auth.reset-password', meta: { guestOnly: true } }
];
