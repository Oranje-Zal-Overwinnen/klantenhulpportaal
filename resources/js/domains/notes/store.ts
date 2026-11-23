import { storeModuleFactory } from '@/services/store';
import type { User } from '@/domains/users/store';

export interface Note {
    id: number;
    ticket_id: number;
    user_id: number;
    body: string;
    author?: User;
    created_at?: string;
    updated_at?: string;
}

export const noteStore = storeModuleFactory<Note>('notes');
