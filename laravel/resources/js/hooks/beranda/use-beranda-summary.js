import useSWR from "swr";
import { fetcher } from "@/utils/api.js";

export const useBerandaSummary = (kelas, jurusan, tahun, bulanSlug) => {
    const swrKey = `/uang-kas/${kelas}/${jurusan}/${tahun}/${bulanSlug}/summary`;
    const { data: summary, error, isLoading } = useSWR(swrKey, fetcher);

    return {
        summary,
        isLoading,
        error,
    };
};
