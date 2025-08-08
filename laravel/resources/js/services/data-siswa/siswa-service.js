import api from "@/utils/api";

export const createSiswa = async (data) => {
    return await api.post("/siswa", data);
};

export const updateSiswa = async (id, data) => {
    return await api.put(`/siswa/${id}`, data);
};

export const deleteSiswa = async (id) => {
    return await api.delete(`/siswa/${id}`);
};
