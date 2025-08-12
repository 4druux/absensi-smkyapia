import useSWR from "swr";
import { fetcher } from '@/utils/api.js';

export const useUangKasClasses = () => {
    const swrKey = "/uang-kas/classes";
    const { data: classes, error, isLoading } = useSWR(swrKey, fetcher);

    return {
        classes,
        isLoading,
        error,
    };
};
