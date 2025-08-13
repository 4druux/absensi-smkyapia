import useSWR from "swr";
import { fetcher } from "@/utils/api";

export const useUangKasSummary = (kelas, jurusan, tahun, bulanSlug) => {
    const swrKey =
        kelas && jurusan && tahun && bulanSlug
            ? `/uang-kas/${kelas}/${jurusan}/${tahun}/${bulanSlug}/summary`
            : null;

    const { data, error, isLoading, mutate } = useSWR(swrKey, fetcher);

    return {
        summary: data,
        isLoading,
        error,
        mutate,
    };
};
