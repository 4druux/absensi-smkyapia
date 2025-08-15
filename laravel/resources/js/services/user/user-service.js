import api from "@/utils/api";

export const getPendingUsers = async () => {
    return await api.get("/users/pending");
};

export const getApprovedUsers = async () => {
    return await api.get("/users/approved");
};

export const approveUser = async (userId) => {
    const csrf_token = document.head.querySelector(
        'meta[name="csrf-token"]'
    ).content;
    return await api.post(`/users/${userId}/approve`, { _token: csrf_token });
};

export const rejectUser = async (userId) => {
    const csrf_token = document.head.querySelector(
        'meta[name="csrf-token"]'
    ).content;
    return await api.delete(`/users/${userId}/reject`, {
        headers: {
            "X-CSRF-TOKEN": csrf_token,
        },
    });
};
