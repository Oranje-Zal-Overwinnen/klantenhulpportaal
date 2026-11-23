import axios from 'axios';
import { setErrorBag, destroyErrors, setMessage, destroyMessage } from '../error';

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
        if (error.response?.status === 422) {
            setErrorBag(error.response.data.errors ?? {});
            setMessage(error.response.data.message);
        }

        return Promise.reject(error);
    }
);

export const getRequest = (endpoint: string, config?: any) => http.get(endpoint, config);
export const postRequest = (endpoint: string, data: any) => http.post(endpoint, data);
export const putRequest = (endpoint: string, data: any) => http.put(endpoint, data);
export const deleteRequest = (endpoint: string, config?: any) => http.delete(endpoint, config);
