import useSWR from "swr";
import { fetcher } from "@/utils/api";

export const useAbsensiClasses = () => {
    const swrKey = "/absensi/classes";
    const { data, error, isLoading } = useSWR(swrKey, fetcher);

    return {
        classes: data,
        isLoading,
        error,
    };
};
