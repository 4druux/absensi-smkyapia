import api from "@/utils/api";

export const getPendingUsers = async (url) => {
    return await api.get(url);
};

export const getApprovedUsers = async (url) => {
    return await api.get(url);
};

export const approveUser = async (userId) => {
    return await api.post(`/users/${userId}/approve`);
};

export const rejectUser = async (userId) => {
    return await api.delete(`/users/${userId}/reject`);
};
