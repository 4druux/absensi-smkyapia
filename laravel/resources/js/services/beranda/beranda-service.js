import api from "@/utils/api";

export const getBerandaClasses = async () => {
    return await api.get("/beranda/classes");
};

export const getBerandaYears = async () => {
    return await api.get("/beranda/years");
};

export const storeBerandaYear = async () => {
    return await api.post("/beranda/years");
};
