import useSWR from "swr";
import toast from "react-hot-toast";
import { fetcher } from '@/utils/api.js';
import { storeGrafikAbsensiYear } from "@/services/grafik/grafik-absensi-service";

export const useGrafikAbsensiYears = () => {
    const swrKey = "/grafik/absensi/years";
    const { data: years, error, isLoading, mutate } = useSWR(swrKey, fetcher);

    const handleAddYear = async () => {
        try {
            const result = await storeGrafikAbsensiYear();
            toast.success(result.message);
            mutate();
        } catch (err) {
            toast.error(
                err.response?.data?.message || "Gagal menambahkan tahun."
            );
        }
    };

    return {
        years,
        isLoading,
        error,
        handleAddYear,
    };
};
