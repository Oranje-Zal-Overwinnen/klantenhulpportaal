import axios from 'axios';
import { computed, ref } from 'vue';
import { getRequest, postRequest } from '@/services/http';

export interface AuthUser {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    role: 'user' | 'admin';
    phone_number: string | null;
}

interface Credentials {
    email: string;
    password: string;
}

const user = ref<AuthUser | null>(null);

const getters = {
    user: computed(() => user.value),
    isAuthenticated: computed(() => user.value !== null),
    isAdmin: computed(() => user.value?.role === 'admin')
};

const actions = {
    fetchUser: async () => {
        try {
            const { data } = await getRequest<AuthUser | { data: AuthUser }>('/user');

            if (!data) {
                user.value = null;
                return false;
            }

            user.value = 'data' in data ? data.data : data;
            return true;
        } catch {
            user.value = null;
            return false;
        }
    },
    login: async (credentials: Credentials) => {
        await axios.get('/sanctum/csrf-cookie');
        await postRequest('/login', credentials);
        return actions.fetchUser();
    },
    logout: async () => {
        try {
            await postRequest('/logout', {});
        } catch {
            // De sessie kan al verlopen zijn; lokaal alsnog uitloggen.
        } finally {
            user.value = null;
        }
    }
};

export const authStore = { getters, actions };
