import api from "@/utils/api";

export const getKelas = async () => {
    return await api.get("/kelas");
};

export const createKelas = async (data) => {
    return await api.post("/kelas", data);
};

export const deleteKelas = async (id) => {
    return await api.delete(`/kelas/${id}`);
};
