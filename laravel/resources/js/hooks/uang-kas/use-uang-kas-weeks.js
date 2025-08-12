import useSWR from "swr";
import { fetcher } from '@/utils/api.js';

export const useUangKasWeeks = (kelas, jurusan, tahun, bulanSlug) => {
    const swrKey = `/uang-kas/${kelas}/${jurusan}/weeks/${tahun}/${bulanSlug}`;
    const { data, error, isLoading, mutate } = useSWR(swrKey, fetcher);

    return {
        weeks: data?.minggu,
        isLoading,
        error,
        mutate,
    };
};
