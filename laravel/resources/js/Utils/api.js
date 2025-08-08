import axios from "axios";

const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content");

const api = axios.create({
    baseURL: "/api",
    headers: {
        "X-Requested-With": "XMLHttpRequest",
        "Content-Type": "application/json",
        ...(csrfToken && { "X-CSRF-TOKEN": csrfToken }),
    },
});

api.interceptors.response.use(
    (response) => {
        return response.data;
    },
    (error) => {
        if (error.response?.status === 401) {
            console.error("Unauthorized! Sesi mungkin telah berakhir.");
        }
        return Promise.reject(error);
    }
);

export const fetcher = (url) => api.get(url);

export default api;
