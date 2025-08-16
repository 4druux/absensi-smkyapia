import useSWR from "swr";
import { fetcher } from "@/utils/api.js";

export const useBerandaAbsensiDays = (kelas, jurusan, tahun, bulanSlug) => {
    const swrKey = `/absensi/${kelas}/${jurusan}/days/${tahun}/${bulanSlug}`;
    const { data, error, isLoading } = useSWR(swrKey, fetcher);

    return {
        days: data?.days,
        absensiDays: data?.absensiDays,
        holidays: data?.holidays,
        isLoading,
        error,
    };
};
