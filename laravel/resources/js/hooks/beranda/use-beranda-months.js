import useSWR from "swr";
import { fetcher } from "@/utils/api.js";

export const useBerandaMonths = (kelas, jurusan, tahun, type) => {
    const swrKey = `/${type}/${kelas}/${jurusan}/months/${tahun}`;
    const { data: months, error, isLoading } = useSWR(swrKey, fetcher);

    return {
        months,
        isLoading,
        error,
    };
};
