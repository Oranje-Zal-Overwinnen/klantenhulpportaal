import axios, { type AxiosRequestConfig } from 'axios';
import { destroyErrors, destroyMessage, setErrorBag, setMessage } from '@/services/error';

const http = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json'
    }
});

http.interceptors.request.use(
    config => {
        destroyErrors(); // Wis oude fouten voordat een nieuw verzoek wordt uitgevoerd
        destroyMessage(); // Wis oude "messages" voordat een nieuw verzoek wordt uitgevoerd
        return config;
    },
    error => Promise.reject(error)
);

http.interceptors.response.use(
    response => response,
    error => {
        const status = error.response?.status;
        const data = error.response?.data;

        if (status === 422) {
            setErrorBag(data?.errors ?? {});
            setMessage(data?.message);
        } else if (status === 401) {
            setMessage(data?.message || 'Je bent niet ingelogd.');
        } else if (status === 403) {
            setMessage(data?.message || 'Je hebt geen toegang tot deze actie.');
        }

        return Promise.reject(error);
    }
);

export const getRequest = <T = unknown>(endpoint: string, config?: AxiosRequestConfig) => http.get<T>(endpoint, config);
export const postRequest = <T = unknown, D = unknown>(endpoint: string, data: D) => http.post<T>(endpoint, data);
export const putRequest = <T = unknown, D = unknown>(endpoint: string, data: D) => http.put<T>(endpoint, data);
export const patchRequest = <T = unknown, D = unknown>(endpoint: string, data: D) => http.patch<T>(endpoint, data);
export const deleteRequest = <T = unknown>(endpoint: string, config?: AxiosRequestConfig) => http.delete<T>(endpoint, config);
export const csrfRequest = () => axios.get('/sanctum/csrf-cookie');
