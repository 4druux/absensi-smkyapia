import useSWR from "swr";
import toast from "react-hot-toast";
import { fetcher } from "@/utils/api";
import { storeKenaikanYear } from "@/services/kenaikan/kenaikan-service";

export const useKenaikanYears = () => {
    const swrKey = "/kenaikan-bersyarat/years";
    const { data: years, error, isLoading, mutate } = useSWR(swrKey, fetcher);

    const handleAddYear = async () => {
        try {
            const result = await storeKenaikanYear();
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
