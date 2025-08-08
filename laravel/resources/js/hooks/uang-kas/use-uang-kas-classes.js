import useSWR from "swr";
import { fetcher } from "@/utils/api";
import { getUangKasClasses } from "@/services/uang-kas/uang-kas-service";

export const useUangKasClasses = () => {
    const swrKey = "/uang-kas/classes";
    const { data: classes, error, isLoading } = useSWR(swrKey, fetcher);

    return {
        classes,
        isLoading,
        error,
    };
};
