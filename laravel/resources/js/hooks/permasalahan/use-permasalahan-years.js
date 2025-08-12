import useSWR from "swr";
import toast from "react-hot-toast";
import { fetcher } from '@/utils/api.js';
import { storePermasalahanYear } from "@/services/permasalahan/permasalahan-service";

export const usePermasalahanYears = () => {
    const swrKey = "/permasalahan/years";
    const { data: years, error, isLoading, mutate } = useSWR(swrKey, fetcher);

    const handleAddYear = async () => {
        try {
            const result = await storePermasalahanYear();
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
