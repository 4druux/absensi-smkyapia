import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.axios.defaults.withCredentials = true;

const csrfToken = document.head.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    window.axios.defaults.headers.common["X-CSRF-TOKEN"] =
        csrfToken.getAttribute("content");
} else {
    console.error(
        "CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token"
    );
}

window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 419) {
            window.location.reload();
            return Promise.reject(error);
        }
        return Promise.reject(error);
    }
);
