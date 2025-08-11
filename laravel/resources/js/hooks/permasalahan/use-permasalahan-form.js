import { useState } from "react";
import { router } from "@inertiajs/react";
import toast from "react-hot-toast";
import {
    storeClassProblem,
    storeStudentProblem,
} from "@/services/permasalahan/permasalahan-service";

export const usePermasalahanForm = () => {
    const [formData, setFormData] = useState({
        tanggal: "",
        masalah: "",
        pemecahan: "",
        siswa_id: "",
        tindakan_walas: "",
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
        setFormData({
            tanggal: "",
            masalah: "",
            pemecahan: "",
            keterangan: "",
            siswa_id: "",
            tindakan_walas: "",
        });
        setErrors({});
    };

    const handleSubmit = async (
        e,
        { isStudentProblem, kelas_id, tahun, onClose }
    ) => {
        e.preventDefault();
        setIsSubmitting(true);
        setErrors({});

        const payload = { ...formData, kelas_id, tahun };

        try {
            const result = isStudentProblem
                ? await storeStudentProblem(payload)
                : await storeClassProblem(payload);

            toast.success(result.message);
            router.reload({ only: ["problems"] });
            resetForm();
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
        setFormData,
        isSubmitting,
        errors,
        handleFormChange,
        handleSubmit,
        resetForm,
    };
};
