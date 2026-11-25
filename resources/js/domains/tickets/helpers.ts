import type { Ticket } from './store';
import type { UserSummary } from '@/domains/users/store';

const statusLabels: Record<Ticket['status'], string> = {
    pending: 'In afwachting',
    in_progress: 'In behandeling',
    resolved: 'Afgehandeld'
};

export const statusLabel = (status: Ticket['status']) => statusLabels[status] ?? status;

export const formatDate = (value?: string) => (value ? new Date(value).toLocaleString('nl-NL') : '-');

export const fullName = (user?: UserSummary | null) => (user ? `${user.first_name} ${user.last_name}` : '-');

export const categoryNames = (ticket: Ticket) =>
    ticket.categories?.length ? ticket.categories.map(category => category.name).join(', ') : '-';
