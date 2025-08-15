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

export const storeSiswaNote = async (
    siswa_id,
    tahun,
    bulan_slug,
    poin_tambahan,
    keterangan
) => {
    return await api.post(route("rekapitulasi.store.note"), {
        siswa_id,
        tahun,
        bulan_slug,
        poin_tambahan,
        keterangan,
    });
};
