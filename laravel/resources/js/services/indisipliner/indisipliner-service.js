import api from "@/utils/api";

export const getIndisiplinerClasses = async () => {
    return await api.get("/indisipliner/classes");
};

export const getIndisiplinerYears = async () => {
    return await api.get("/indisipliner/years");
};

export const storeIndisiplinerYear = async () => {
    return await api.post("/indisipliner/years");
};

export const getIndisiplinerData = async ([url, params]) => {
    return await api.get(url, { params });
};

export const storeIndisipliner = async (payload) => {
    return await api.post("/indisipliner/data", payload);
};

export const deleteIndisipliner = async (id) => {
    return await api.delete(`/indisipliner/data/${id}`);
};
