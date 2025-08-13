import useSWR from "swr";
import { fetcher } from "@/utils/api.js";
import toast from "react-hot-toast";
import {
    storeHoliday,
    deleteHoliday,
} from "@/services/absensi/absensi-service";

export const useAbsensiDays = (kelas, jurusan, tahun, bulanSlug) => {
    const swrKey = `/absensi/${kelas}/${jurusan}/days/${tahun}/${bulanSlug}`;
    const { data, error, isLoading, mutate } = useSWR(swrKey, fetcher);

    const handleSetHoliday = async (dayNumber) => {
        const confirmHoliday = confirm(
            `Apakah Anda yakin ingin menetapkan tanggal ${dayNumber} sebagai hari libur?`
        );
        if (confirmHoliday) {
            try {
                const result = await storeHoliday(
                    kelas,
                    jurusan,
                    tahun,
                    bulanSlug,
                    dayNumber
                );
                toast.success(result.message);
                mutate();
            } catch (err) {
                toast.error(
                    err.response?.data?.message ||
                        "Gagal menetapkan hari libur."
                );
            }
        }
    };

    const handleCancelHoliday = async (dayNumber) => {
        const confirmCancel = confirm(
            `Apakah Anda yakin ingin membatalkan hari libur pada tanggal ${dayNumber}?`
        );
        if (confirmCancel) {
            try {
                const result = await deleteHoliday(
                    kelas,
                    jurusan,
                    tahun,
                    bulanSlug,
                    dayNumber
                );
                toast.success(result.message);
                mutate();
            } catch (err) {
                toast.error(
                    err.response?.data?.message ||
                        "Gagal membatalkan hari libur."
                );
            }
        }
    };

    return {
        days: data?.days,
        absensiDays: data?.absensiDays,
        holidays: data?.holidays,
        isLoading,
        error,
        handleSetHoliday,
        handleCancelHoliday,
        mutate,
    };
};
