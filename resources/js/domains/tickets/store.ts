import { storeModuleFactory } from '@/services/store';
import { getRequest, patchRequest, postRequest, putRequest } from '@/services/http';
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

export interface TicketFormData {
    title: string;
    description: string;
    categories: number[];
}

const factory = storeModuleFactory<Ticket>('tickets');

export const ticketStore = {
    ...factory,
    actions: {
        ...factory.actions,
        createTicket: async (data: TicketFormData) => {
            const { data: res } = await postRequest<{ data: Ticket[] }>('/tickets', data);

            if (!res) return;

            factory.setters.setAll(res.data);
        },
        fetchTicket: async (id: number) => {
            const { data } = await getRequest<Ticket | { data: Ticket }>(`/tickets/${id}`);

            if (!data) return null;

            const ticket = 'data' in data ? data.data : data;
            factory.setters.setAll([ticket]);

            return ticket;
        },
        updateTicket: async (id: number, data: TicketFormData) => {
            const { data: res } = await putRequest<{ data: Ticket[] }>(`/tickets/${id}`, data);

            if (!res) return;

            factory.setters.setAll(res.data);
        },
        assignTicket: async (id: number, assignedTo: number | null) => {
            const { data } = await patchRequest<Ticket | { data: Ticket }>(`/tickets/${id}/assignment`, {
                assigned_to: assignedTo
            });

            if (!data) return null;

            const ticket = 'data' in data ? data.data : data;
            factory.setters.setAll([ticket]);

            return ticket;
        },
        updateStatus: async (id: number, status: Ticket['status']) => {
            const { data } = await patchRequest<Ticket | { data: Ticket }>(`/tickets/${id}/status`, {
                status
            });

            if (!data) return null;

            const ticket = 'data' in data ? data.data : data;
            factory.setters.setAll([ticket]);

            return ticket;
        },
        createReply: async (ticketId: number, body: string) => {
            const { data } = await postRequest<Ticket | { data: Ticket }>(`/tickets/${ticketId}/replies`, {
                body
            });

            if (!data) return null;

            const ticket = 'data' in data ? data.data : data;
            factory.setters.setAll([ticket]);

            return ticket;
        },
        updateReply: async (replyId: number, body: string) => {
            const { data } = await putRequest<Ticket | { data: Ticket }>(`/replies/${replyId}`, {
                body
            });

            if (!data) return null;

            const ticket = 'data' in data ? data.data : data;
            factory.setters.setAll([ticket]);

            return ticket;
        }
    }
};
