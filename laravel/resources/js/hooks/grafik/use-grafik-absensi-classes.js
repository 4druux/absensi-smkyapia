import useSWR from "swr";
import { fetcher } from "@/utils/api";

export const useGrafikAbsensiClasses = () => {
    const swrKey = "/grafik/absensi/classes";
    const { data, error, isLoading } = useSWR(swrKey, fetcher);

    return {
        classes: data,
        isLoading,
        error,
    };
};
