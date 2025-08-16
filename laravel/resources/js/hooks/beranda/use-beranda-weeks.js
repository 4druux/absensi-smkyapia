import useSWR from "swr";
import { fetcher } from "@/utils/api.js";

export const useBerandaWeeks = (kelas, jurusan, tahun, bulanSlug) => {
    const swrKey = `/uang-kas/${kelas}/${jurusan}/weeks/${tahun}/${bulanSlug}`;
    const { data, error, isLoading } = useSWR(swrKey, fetcher);

    return {
        weeks: data?.minggu,
        isLoading,
        error,
    };
};