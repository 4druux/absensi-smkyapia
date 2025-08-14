import { useState, useCallback } from "react";
import toast from "react-hot-toast";
import { storeIndisipliner } from "@/services/indisipliner/indisipliner-service";

export const useIndisiplinerForm = () => {
    const [formData, setFormData] = useState({
        siswa_id: "",
        jenis_surat: "",
        nomor_surat: "",
        tanggal_surat: "",
        terlambat_alasan: "",
        terlambat_poin: "",
        alfa_alasan: "",
        alfa_poin: "",
        bolos_alasan: "",
        bolos_poin: "",
        details: [{ jenis_pelanggaran: "", alasan: "", poin: "" }],
    });

    const [isSubmitting, setIsSubmitting] = useState(false);
    const [errors, setErrors] = useState({});

    const handleFormChange = (key, value) => {
        setFormData((prev) => ({ ...prev, [key]: value }));
    };

    const resetForm = useCallback(() => {
        setFormData({
            siswa_id: "",
            jenis_surat: "",
            nomor_surat: "",
            tanggal_surat: "",
            terlambat_alasan: "",
            terlambat_poin: "",
            alfa_alasan: "",
            alfa_poin: "",
            bolos_alasan: "",
            bolos_poin: "",
            details: [{ jenis_pelanggaran: "", alasan: "", poin: "" }],
        });
        setErrors({});
    }, []);

    const handleSubmit = async (
        e,
        { selectedClassId, tahun, onClose, mutate }
    ) => {
        e.preventDefault();
        setIsSubmitting(true);
        setErrors({});

        try {
            await storeIndisipliner({
                ...formData,
                kelas_id: selectedClassId,
                tahun,
            });
            toast.success("Data indisipliner berhasil disimpan!");
            resetForm();
            onClose();
            mutate();
        } catch (err) {
            const serverErrors = err.response?.data?.errors;
            if (serverErrors) {
                setErrors(serverErrors);
            }
            toast.error("Gagal menyimpan data indisipliner.");
        } finally {
            setIsSubmitting(false);
        }
    };

    return {
        formData,
        isSubmitting,
        errors,
        handleFormChange,
        handleSubmit,
        resetForm,
        setFormData,
    };
};
