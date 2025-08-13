import useSWR from "swr";
import toast from "react-hot-toast";
import { getKelas, deleteKelas } from "@/services/data-siswa/kelas-service";

export const useKelas = () => {
    const swrKey = "/kelas";
    const {
        data: allKelas,
        error,
        isLoading,
        mutate,
    } = useSWR(swrKey, getKelas);

    const handleDeleteKelas = async (kelasId, className) => {
        if (
            confirm(
                `Apakah Anda yakin ingin menghapus kelas ${className} beserta semua data siswanya?`
            )
        ) {
            try {
                const result = await deleteKelas(kelasId);
                toast.success(result.message);
                mutate();
            } catch (error) {
                toast.error("Gagal menghapus kelas."), error;
            }
        }
    };

    return {
        allKelas,
        isLoading,
        error,
        handleDeleteKelas,
    };
};
