import useSWR from "swr";
import toast from "react-hot-toast";
import { fetcher } from "@/utils/api.js";
import { storeUangKasYear } from "@/services/uang-kas/uang-kas-service";

export const useUangKasYears = () => {
    const swrKey = "/uang-kas/years";
    const { data: years, error, isLoading, mutate } = useSWR(swrKey, fetcher);

    const handleAddYear = async () => {
        try {
            const result = await storeUangKasYear();
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
