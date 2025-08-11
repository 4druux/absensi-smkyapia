import api from "@/utils/api";

export const getGrafikAbsensiClasses = async () => {
    return await api.get("/grafik/absensi/classes");
};

export const getGrafikAbsensiYears = async () => {
    return await api.get("/grafik/absensi/years");
};

export const storeGrafikAbsensiYear = async () => {
    return await api.post("/grafik/absensi/years");
};

export const getGrafikAbsensiYearlyData = async (kelas, jurusan, tahun) => {
    return await api.get(
        `/grafik/absensi/${kelas}/${jurusan}/${tahun}/yearly-data`
    );
};
