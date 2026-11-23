import { storeModuleFactory } from '@/services/store';

export interface User {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    role: 'user' | 'admin';
    phone_number: string | null;
    created_at?: string;
    updated_at?: string;
}

export const userStore = storeModuleFactory<User>('users');

export type UserFormData = Pick<User, 'first_name' | 'last_name' | 'email' | 'role' | 'phone_number'>;
