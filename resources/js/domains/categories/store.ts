import { storeModuleFactory } from '@/services/store';

export interface Category {
    id: number;
    name: string;
}

export const categoryStore = storeModuleFactory<Category>('categories');
