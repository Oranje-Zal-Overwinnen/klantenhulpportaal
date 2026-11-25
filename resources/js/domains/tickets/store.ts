import { storeModuleFactory } from '@/services/store';
import { deleteRequest, getRequest, patchRequest, postRequest, putRequest } from '@/services/http';
import type { Category } from '@/domains/categories/store';
import type { UserSummary } from '@/domains/users/store';

export type TicketStatus = 'pending' | 'in_progress' | 'resolved';

export const TICKET_STATUSES: TicketStatus[] = ['pending', 'in_progress', 'resolved'];

export interface TicketReply {
    id: number;
    ticket_id: number;
    user_id: number;
    body: string;
    author?: UserSummary;
    created_at?: string;
    updated_at?: string;
}

export interface TicketNote {
    id: number;
    ticket_id: number;
    user_id: number;
    body: string;
    author?: UserSummary;
    created_at?: string;
    updated_at?: string;
}

export interface Ticket {
    id: number;
    title: string;
    description: string;
    status: TicketStatus;
    user_id: number;
    assigned_to: number | null;
    creator?: UserSummary;
    assignee?: UserSummary | null;
    categories?: Category[];
    replies?: TicketReply[];
    notes?: TicketNote[];
    created_at?: string;
    updated_at?: string;
}

export interface TicketFormData {
    title: string;
    description: string;
    categories: number[];
}

type TicketResponse = Ticket | { data: Ticket };

const factory = storeModuleFactory<Ticket>('tickets');

const applyTicket = (payload: TicketResponse) => {
    const ticket = 'data' in payload ? payload.data : payload;

    factory.setters.setOne(ticket);

    return ticket;
};

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
            factory.setters.reset();

            const { data } = await getRequest<TicketResponse>(`/tickets/${id}`);

            if (!data) return null;

            return applyTicket(data);
        },
        updateTicket: async (id: number, data: TicketFormData) => {
            const { data: res } = await putRequest<{ data: Ticket[] }>(`/tickets/${id}`, data);

            if (!res) return;

            factory.setters.setAll(res.data);
        },
        assignTicket: async (id: number, assignedTo: number | null) => {
            const { data } = await patchRequest<TicketResponse>(`/tickets/${id}/assignment`, {
                assigned_to: assignedTo
            });

            if (!data) return null;

            return applyTicket(data);
        },
        updateStatus: async (id: number, status: TicketStatus) => {
            const { data } = await patchRequest<TicketResponse>(`/tickets/${id}/status`, {
                status
            });

            if (!data) return null;

            return applyTicket(data);
        },
        createReply: async (ticketId: number, body: string) => {
            const { data } = await postRequest<TicketResponse>(`/tickets/${ticketId}/replies`, {
                body
            });

            if (!data) return null;

            return applyTicket(data);
        },
        updateReply: async (replyId: number, body: string) => {
            const { data } = await putRequest<TicketResponse>(`/replies/${replyId}`, {
                body
            });

            if (!data) return null;

            return applyTicket(data);
        },
        createNote: async (ticketId: number, body: string) => {
            const { data } = await postRequest<TicketResponse>(`/tickets/${ticketId}/notes`, {
                body
            });

            if (!data) return null;

            return applyTicket(data);
        },
        updateNote: async (noteId: number, body: string) => {
            const { data } = await putRequest<TicketResponse>(`/notes/${noteId}`, {
                body
            });

            if (!data) return null;

            return applyTicket(data);
        },
        deleteNote: async (noteId: number) => {
            try {
                const { data } = await deleteRequest<TicketResponse>(`/notes/${noteId}`);

                if (!data) return false;

                applyTicket(data);

                return true;
            } catch {
                return false;
            }
        }
    }
};
