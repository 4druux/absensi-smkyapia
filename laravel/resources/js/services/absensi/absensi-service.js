import api from "@/utils/api";

export const getAbsensiClasses = async () => {
    return await api.get("/absensi/classes");
};

export const getAbsensiYears = async () => {
    return await api.get("/absensi/years");
};

export const storeAbsensiYear = async (payload) => {
    return await api.post("/absensi/years", payload);
};

export const getAbsensiMonths = async (kelas, jurusan, tahun) => {
    return await api.get(`/absensi/${kelas}/${jurusan}/months/${tahun}`);
};

export const getAbsensiDays = async (kelas, jurusan, tahun, bulanSlug) => {
    return await api.get(
        `/absensi/${kelas}/${jurusan}/days/${tahun}/${bulanSlug}`
    );
};

export const getDailyAttendance = async (
    kelas,
    jurusan,
    tahun,
    bulanSlug,
    tanggal
) => {
    return await api.get(
        `/absensi/${kelas}/${jurusan}/attendance/${tahun}/${bulanSlug}/${tanggal}`
    );
};

export const storeDailyAttendance = async (
    kelas,
    jurusan,
    tahun,
    bulanSlug,
    tanggal,
    payload
) => {
    return await api.post(
        `/absensi/${kelas}/${jurusan}/attendance/${tahun}/${bulanSlug}/${tanggal}`,
        payload
    );
};

export const storeHoliday = async (
    kelas,
    jurusan,
    tahun,
    bulanSlug,
    tanggal
) => {
    return await api.post(
        `/absensi/${kelas}/${jurusan}/holidays/${tahun}/${bulanSlug}/${tanggal}`
    );
};

export const deleteHoliday = async (
    kelas,
    jurusan,
    tahun,
    bulanSlug,
    tanggal
) => {
    return await api.delete(
        `/absensi/${kelas}/${jurusan}/holidays/${tahun}/${bulanSlug}/${tanggal}`
    );
};
