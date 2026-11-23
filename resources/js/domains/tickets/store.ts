
import { storeModuleFactory } from '@/services/store';

interface Ticket { }

export const ticketStore = storeModuleFactory<Ticket>('tickets');
