import useSWR from "swr";
import { fetcher } from "@/utils/api";

export const useRekapitulasiClasses = () => {
    const swrKey = "/rekapitulasi/classes";
    const { data, error, isLoading } = useSWR(swrKey, fetcher);

    return {
        classes: data,
        isLoading,
        error,
    };
};
