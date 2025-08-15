import toast from "react-hot-toast";
import { storeSiswaNote } from "@/services/rekapitulasi/rekap-service";

export const useSiswaNotes = (tahun, bulanSlug) => {
    const handleStoreNote = async (siswaId, poin, keterangan) => {
        try {
            await storeSiswaNote(siswaId, tahun, bulanSlug, poin, keterangan);
            toast.success("Poin dan keterangan berhasil disimpan!");
        } catch (err) {
            toast.error(err.response?.data?.message || "Gagal menyimpan data.");
        }
    };

    return {
        handleStoreNote,
    };
};
