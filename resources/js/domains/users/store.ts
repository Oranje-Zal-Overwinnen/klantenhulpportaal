import { storeModuleFactory } from '@/services/store';

export interface UserSummary {
    id: number;
    first_name: string;
    last_name: string;
    role: 'user' | 'admin';
}

export interface User extends UserSummary {
    email: string;
    phone_number: string | null;
    created_at?: string;
    updated_at?: string;
}

export const userStore = storeModuleFactory<User>('users');

export type UserFormData = Pick<User, 'first_name' | 'last_name' | 'email' | 'role' | 'phone_number'>;
