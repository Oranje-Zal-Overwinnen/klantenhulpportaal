import { storeModuleFactory } from '@/services/store';

interface Note { }

export const noteStore = storeModuleFactory<Note>('notes');
