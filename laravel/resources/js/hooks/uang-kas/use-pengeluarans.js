import useSWR from "swr";
import { fetcher } from "@/utils/api";

export const usePengeluarans = (kelas, jurusan, tahun, bulanSlug) => {
    const swrKey =
        kelas && jurusan && tahun && bulanSlug
            ? `/pengeluaran/${kelas}/${jurusan}/${tahun}/${bulanSlug}`
            : null;

    const { data, error, isLoading, mutate } = useSWR(swrKey, fetcher);

    return {
        pengeluarans: data?.pengeluarans,
        totalPengeluaran: data?.total_pengeluaran,
        isLoading,
        error,
        mutate,
    };
};
