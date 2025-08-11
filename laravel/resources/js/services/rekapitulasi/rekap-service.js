import api from "@/utils/api";

export const getRekapitulasiClasses = async () => {
    return await api.get("/rekapitulasi/classes");
};

export const getRekapitulasiYears = async () => {
    return await api.get("/rekapitulasi/years");
};

export const storeRekapitulasiYear = async () => {
    return await api.post("/rekapitulasi/years");
};

export const getRekapitulasiMonths = async (kelas, jurusan, tahun) => {
    return await api.get(`/rekapitulasi/${kelas}/${jurusan}/months/${tahun}`);
};
