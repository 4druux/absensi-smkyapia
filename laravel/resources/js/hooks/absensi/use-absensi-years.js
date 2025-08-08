import useSWR from "swr";
import toast from "react-hot-toast";
import { fetcher } from "@/utils/api";
import { storeAbsensiYear } from "@/services/absensi/absensi-service";

export const useAbsensiYears = () => {
    const swrKey = "/absensi/years";
    const { data: years, error, isLoading, mutate } = useSWR(swrKey, fetcher);

    const handleAddYear = async () => {
        try {
            const result = await storeAbsensiYear();
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
