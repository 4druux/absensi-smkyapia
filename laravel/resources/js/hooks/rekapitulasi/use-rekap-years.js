import { useState } from "react";
import useSWR from "swr";
import toast from "react-hot-toast";
import { fetcher } from "@/utils/api";
import {
    getRekapitulasiYears,
    storeRekapitulasiYear,
} from "@/services/rekapitulasi/rekap-service";

export const useRekapitulasiYears = () => {
    const swrKey = "/rekapitulasi/years";
    const { data: years, error, isLoading, mutate } = useSWR(swrKey, fetcher);
    const [downloadingStatus, setDownloadingStatus] = useState({});

    const handleAddYear = async () => {
        try {
            const result = await storeRekapitulasiYear();
            toast.success(result.message);
            mutate();
        } catch (err) {
            toast.error(
                err.response?.data?.message || "Gagal menambahkan tahun."
            );
        }
    };

    const handleExportRekapitulasi = async (kelas, jurusan, tahun, format) => {
        const key = `${tahun}-${format}`;
        if (downloadingStatus[key]) return;

        setDownloadingStatus((prev) => ({ ...prev, [key]: true }));

        const url = `/rekapitulasi/${kelas}/${jurusan}/${tahun}/export/${format}`;

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
                    : `Rekapitulasi-${kelas}-${jurusan}-${tahun}.${
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
        years,
        isLoading,
        error,
        handleAddYear,
        handleExportRekapitulasi,
        downloadingStatus,
    };
};
