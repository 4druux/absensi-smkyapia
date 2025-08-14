import useSWR from "swr";
import { getIndisiplinerData } from "@/services/indisipliner/indisipliner-service";
import toast from "react-hot-toast";
import { useState } from "react";

export const useIndisiplinerData = (kelasId, tahun) => {
    const swrKey =
        kelasId && tahun
            ? ["/indisipliner/data", { kelas_id: kelasId, tahun }]
            : null;

    const {
        data: indisiplinerData,
        error,
        isLoading,
        mutate,
    } = useSWR(swrKey, getIndisiplinerData, {
        revalidateOnFocus: false,
    });

    const [downloadingStatus, setDownloadingStatus] = useState({});

    const handleExportStudent = async (id, studentName, noUrut) => {
        const key = `${id}-pdf`;
        if (downloadingStatus[key]) {
            return;
        }

        setDownloadingStatus((prev) => ({ ...prev, [key]: true }));

        const url = `/data-indisipliner/siswa/${id}/${tahun}/${noUrut}/export/pdf`;

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
                    : `Data-Indisipliner-${studentName}.pdf`;

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
        indisiplinerData,
        isLoading,
        error,
        mutate,
        handleExportStudent,
    };
};
