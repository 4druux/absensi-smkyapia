import useSWR from "swr";
import { fetcher } from '@/utils/api.js';

export const useIndisiplinerClasses = () => {
    const swrKey = "/indisipliner/classes";
    const { data, error, isLoading } = useSWR(swrKey, fetcher);

    return {
        classes: data,
        isLoading,
        error,
    };
};