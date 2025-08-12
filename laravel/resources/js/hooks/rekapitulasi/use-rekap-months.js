import { useState } from "react";
import useSWR from "swr";
import toast from "react-hot-toast";
import { fetcher } from '@/utils/api.js';

export const useRekapitulasiMonths = (kelas, jurusan, tahun) => {
    const swrKey = `/rekapitulasi/${kelas}/${jurusan}/months/${tahun}`;
    const { data: months, error, isLoading } = useSWR(swrKey, fetcher);

    const [downloadingStatus, setDownloadingStatus] = useState({});

    const handleExport = async (monthSlug, format) => {
        const key = `${monthSlug}-${format}`;
        if (downloadingStatus[key]) return;

        setDownloadingStatus((prev) => ({ ...prev, [key]: true }));

        const url = route(`rekapitulasi.month.export.${format}`, {
            kelas,
            jurusan,
            tahun,
            bulanSlug: monthSlug,
        });

        try {
            const response = await fetch(url);
            const contentType = response.headers.get("Content-Type");

            if (contentType && contentType.includes("application/json")) {
                const errorData = await response.json();
                toast.error(errorData.error);
            } else if (response.ok) {
                const blob = await response.blob();
                const downloadUrl = window.URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = downloadUrl;

                const contentDisposition = response.headers.get(
                    "Content-Disposition"
                );
                const filenameMatch =
                    contentDisposition &&
                    contentDisposition.match(/filename="([^"]+)"/);
                a.download = filenameMatch
                    ? filenameMatch[1]
                    : `Rekapitulasi-${monthSlug}-${tahun}.${
                          format === "excel" ? "xlsx" : "pdf"
                      }`;

                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(downloadUrl);
                toast.success("File berhasil diunduh!");
            } else {
                toast.error("Gagal mengekspor data. Terjadi kesalahan server.");
            }
        } catch (error) {
            console.error("Error during export:", error);
            toast.error("Gagal mengekspor data. Terjadi kesalahan.");
        } finally {
            setDownloadingStatus((prev) => ({ ...prev, [key]: false }));
        }
    };

    return {
        months,
        isLoading,
        error,
        handleExport,
        downloadingStatus,
    };
};
