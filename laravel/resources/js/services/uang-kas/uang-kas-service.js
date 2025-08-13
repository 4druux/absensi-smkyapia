import api from "@/utils/api";

export const getUangKasClasses = async () => {
    return await api.get("/uang-kas/classes");
};

export const getUangKasYears = async () => {
    return await api.get("/uang-kas/years");
};

export const storeUangKasYear = async () => {
    return await api.post("/uang-kas/years");
};

export const getUangKasMonths = async (kelas, jurusan, tahun) => {
    return await api.get(`/uang-kas/${kelas}/${jurusan}/months/${tahun}`);
};

export const getUangKasWeeks = async (kelas, jurusan, tahun, bulanSlug) => {
    return await api.get(
        `/uang-kas/${kelas}/${jurusan}/weeks/${tahun}/${bulanSlug}`
    );
};

export const getWeeklyPayments = async (
    kelas,
    jurusan,
    tahun,
    bulanSlug,
    minggu
) => {
    return await api.get(
        `/uang-kas/${kelas}/${jurusan}/payments/${tahun}/${bulanSlug}/${minggu}`
    );
};

export const storeWeeklyPayments = async (
    kelas,
    jurusan,
    tahun,
    bulanSlug,
    minggu,
    payload
) => {
    return await api.post(
        `/uang-kas/${kelas}/${jurusan}/payments/${tahun}/${bulanSlug}/${minggu}`,
        payload
    );
};

export const storeHoliday = async (
    kelas,
    jurusan,
    tahun,
    bulanSlug,
    minggu
) => {
    return await api.post(
        `/uang-kas/${kelas}/${jurusan}/holidays/${tahun}/${bulanSlug}/${minggu}`
    );
};

export const getUangKasOther = async (kelas, jurusan, tahun, bulanSlug) => {
    return await api.get(
        `/uang-kas/${kelas}/${jurusan}/other-cash/${tahun}/${bulanSlug}`
    );
};

export const storeUangKasOther = async (
    payload,
    kelas,
    jurusan,
    displayYear,
    bulanSlug
) => {
    return await api.post(
        `/uang-kas/${kelas}/${jurusan}/other-cash/${displayYear}/${bulanSlug}`,
        payload
    );
};

export const getOtherPayments = async (kelas, jurusan, iuranId) => {
    return await api.get(
        `/uang-kas/${kelas}/${jurusan}/other-cash/${iuranId}/payments`
    );
};

export const storeOtherPayments = async (kelas, jurusan, iuranId, payload) => {
    return await api.post(
        `/uang-kas/${kelas}/${jurusan}/other-cash/${iuranId}/payments`,
        payload
    );
};

export const storePengeluaran = async (
    payload,
    kelas,
    jurusan,
    displayYear,
    bulanSlug
) => {
    return await api.post(
        `/pengeluaran/${kelas}/${jurusan}/${displayYear}/${bulanSlug}`,
        payload
    );
};
