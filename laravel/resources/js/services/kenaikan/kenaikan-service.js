import api from "@/utils/api";

export const getKenaikanClasses = async () => {
    return await api.get("/kenaikan-bersyarat/classes");
};

export const getKenaikanYears = async () => {
    return await api.get("/kenaikan-bersyarat/years");
};

export const storeKenaikanYear = async () => {
    return await api.post("/kenaikan-bersyarat/years");
};

export const getKenaikanStudentData = async (siswaId, tahun) => {
    return await api.get(`/kenaikan-bersyarat/student-data/${siswaId}/${tahun}`);
};

export const storeKenaikanStudentData = async (siswaId, tahun, payload) => {
    return await api.post(`/kenaikan-bersyarat/student-data/${siswaId}/${tahun}`, payload);
};