import api from "@/utils/api";

export const getJurusans = async () => {
    return await api.get("/jurusan");
};

export const getKelasByJurusan = async (jurusanId) => {
    return await api.get(`/jurusan/${jurusanId}/kelas`);
};

export const createJurusan = async (data) => {
    return await api.post("/jurusan", data);
};

export const updateJurusan = async (id, data) => {
    return await api.put(`/jurusan/${id}`, data);
};

export const deleteJurusan = async (id) => {
    return await api.delete(`/jurusan/${id}`);
};
