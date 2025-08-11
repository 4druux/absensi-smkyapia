import { useState } from "react";
import toast from "react-hot-toast";

export const useKenaikanExport = (kelas, jurusan, tahun) => {
    const [downloadingStatus, setDownloadingStatus] = useState({});

    const handleExport = async (format) => {
        const key = `${tahun}-${format}`;
        if (downloadingStatus[key]) return;

        setDownloadingStatus((prev) => ({ ...prev, [key]: true }));

        const url = `/kenaikan-bersyarat/${kelas}/${jurusan}/${tahun}/export/${format}`;

        try {
            const response = await fetch(url);
            const contentType = response.headers.get("Content-Type");

            if (contentType && contentType.includes("application/json")) {
                const errorData = await response.json();
                toast.error(errorData.error || "Gagal mengekspor data.");
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
                    : `Kenaikan-Bersyarat-${kelas}-${jurusan}-${tahun}.${
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
        handleExport,
        downloadingStatus,
    };
};
