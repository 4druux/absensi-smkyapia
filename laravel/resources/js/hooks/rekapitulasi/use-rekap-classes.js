import useSWR from "swr";
import { fetcher } from '@/utils/api.js';

export const useRekapitulasiClasses = () => {
    const swrKey = "/rekapitulasi/classes";
    const { data, error, isLoading } = useSWR(swrKey, fetcher);

    return {
        classes: data,
        isLoading,
        error,
    };
};
