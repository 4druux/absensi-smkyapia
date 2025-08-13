import { useState } from "react";
import toast from "react-hot-toast";
import { storePengeluaran } from "@/services/uang-kas/uang-kas-service";

export const usePengeluaranForm = () => {
    const [formData, setFormData] = useState({
        tanggal: "",
        deskripsi: "",
        nominal: "",
    });
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [errors, setErrors] = useState({});

    const handleFormChange = (name, value) => {
        setFormData((prev) => ({ ...prev, [name]: value }));
        if (errors[name]) {
            setErrors((prev) => ({ ...prev, [name]: null }));
        }
    };

    const resetForm = () => {
        setFormData({ tanggal: "", deskripsi: "", nominal: "" });
        setErrors({});
    };

    const handleSubmit = async (
        e,
        { kelas, jurusan, displayYear, bulanSlug, onClose, onSuccess }
    ) => {
        e.preventDefault();
        setIsSubmitting(true);
        setErrors({});

        try {
            const result = await storePengeluaran(
                formData,
                kelas,
                jurusan,
                displayYear,
                bulanSlug
            );

            toast.success(result.message);
            resetForm();
            await onSuccess();
            onClose();
        } catch (err) {
            if (err.response?.status === 422) {
                setErrors(err.response.data.errors);
                toast.error("Data yang diberikan tidak valid.");
            } else {
                toast.error(
                    err.response?.data?.message || "Gagal menyimpan data."
                );
            }
        } finally {
            setIsSubmitting(false);
        }
    };

    return {
        formData,
        errors,
        isSubmitting,
        handleFormChange,
        handleSubmit,
        resetForm,
    };
};
