import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.axios.defaults.withCredentials = true;

window.axios.interceptors.request.use(
    (config) => {
        const csrfToken = document.head.querySelector(
            'meta[name="csrf-token"]'
        );

        if (csrfToken) {
            config.headers["X-CSRF-TOKEN"] = csrfToken.getAttribute("content");
        }

        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);
