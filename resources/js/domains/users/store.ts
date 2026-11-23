
import { storeModuleFactory } from '@/services/store';

interface User { }

export const userStore = storeModuleFactory<User>('users');
