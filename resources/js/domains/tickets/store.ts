import { storeModuleFactory } from '@/services/store';
import type { Category } from '@/domains/categories/store';
import type { Reply } from '@/domains/replies/store';
import type { User } from '@/domains/users/store';

export interface Ticket {
    id: number;
    title: string;
    description: string;
    status: 'pending' | 'in_progress' | 'resolved';
    user_id: number;
    assigned_to: number | null;
    creator?: User;
    assignee?: User | null;
    categories?: Category[];
    replies?: Reply[];
    created_at?: string;
    updated_at?: string;
}

export const ticketStore = storeModuleFactory<Ticket>('tickets');
