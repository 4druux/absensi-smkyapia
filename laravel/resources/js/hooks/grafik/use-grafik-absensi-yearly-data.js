import { useState } from "react";
import useSWR from "swr";
import toast from "react-hot-toast";
import { fetcher } from '@/utils/api.js';

export const useGrafikAbsensiYearlyData = (kelas, jurusan, tahun) => {
    const swrKey = `/grafik/absensi/${kelas}/${jurusan}/${tahun}/yearly-data`;
    const { data, error, isLoading } = useSWR(swrKey, fetcher, {
        revalidateOnFocus: false,
    });

    const [downloadingStatus, setDownloadingStatus] = useState({});

    const handleExport = async (format) => {
        const key = `${tahun}-${format}`;
        if (downloadingStatus[key]) return;

        setDownloadingStatus((prev) => ({ ...prev, [key]: true }));

        const url = `/grafik/absensi/${kelas}/${jurusan}/${tahun}/export/${format}`;

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
                    : `Grafik-Absensi-${kelas}-${jurusan}-${tahun}.${
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
        } catch (err) {
            console.error("Export error:", err);
            toast.error("Gagal mengekspor data. Terjadi kesalahan.");
        } finally {
            setDownloadingStatus((prev) => ({ ...prev, [key]: false }));
        }
    };

    return {
        chartData: data,
        isLoading,
        error,
        handleExport,
        downloadingStatus,
    };
};
