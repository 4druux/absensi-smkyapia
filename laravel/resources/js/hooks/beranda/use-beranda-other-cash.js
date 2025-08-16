import useSWR from "swr";
import { fetcher } from "@/utils/api.js";

export const useBerandaOtherCash = (kelas, jurusan, tahun, bulanSlug) => {
    const swrKey = `/uang-kas/${kelas}/${jurusan}/other-cash/${tahun}/${bulanSlug}`;
    const { data: otherCashData, error, isLoading } = useSWR(swrKey, fetcher);

    return {
        otherCashData,
        isLoading,
        error,
    };
};
