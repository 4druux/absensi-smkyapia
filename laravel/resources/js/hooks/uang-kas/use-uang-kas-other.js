import useSWR from "swr";
import { fetcher } from "@/utils/api.js";

export const useUangKasOther = (kelas, jurusan, tahun, bulanSlug) => {
    const swrKey = `/uang-kas/${kelas}/${jurusan}/other-cash/${tahun}/${bulanSlug}`;
    const { data, error, isLoading, mutate } = useSWR(swrKey, fetcher);

    return {
        otherCashData: data,
        isLoading,
        error,
        mutate,
    };
};
