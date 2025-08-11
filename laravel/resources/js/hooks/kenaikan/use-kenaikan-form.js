import { useState, useEffect } from "react";
import useSWR from "swr";
import toast from "react-hot-toast";
import { fetcher } from "@/utils/api";
import { storeKenaikanStudentData } from "@/services/kenaikan/kenaikan-service";
import { router } from "@inertiajs/react";

export const useKenaikanForm = (siswaId, tahun) => {
    const swrKey =
        siswaId && tahun
            ? `/kenaikan-bersyarat/student-data/${siswaId}/${tahun}`
            : null;
    const { data: initialData, error, isLoading } = useSWR(swrKey, fetcher);

    const [formData, setFormData] = useState({
        jumlah_nilai_kurang: "",
        akhlak: "",
        rekomendasi_walas: "",
        keputusan_akhir: "",
    });

    const [isSubmitting, setIsSubmitting] = useState(false);
    const [errors, setErrors] = useState({});

    useEffect(() => {
        if (initialData?.savedData) {
            setFormData({
                jumlah_nilai_kurang:
                    initialData.savedData.jumlah_nilai_kurang || "",
                akhlak: initialData.savedData.akhlak || "",
                rekomendasi_walas:
                    initialData.savedData.rekomendasi_walas || "",
                keputusan_akhir: initialData.savedData.keputusan_akhir || "",
            });
        }
    }, [initialData]);

    const handleFormChange = (name, value) => {
        setFormData((prev) => ({ ...prev, [name]: value }));
        if (errors[name]) {
            setErrors((prev) => ({ ...prev, [name]: null }));
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setErrors({});

        const newErrors = {};
        if (!formData.jumlah_nilai_kurang)
            newErrors.jumlah_nilai_kurang = "Jumlah nilai kurang wajib diisi.";
        if (!formData.akhlak) newErrors.akhlak = "Akhlak wajib dipilih.";
        if (!formData.rekomendasi_walas)
            newErrors.rekomendasi_walas = "Rekomendasi WALAS wajib dipilih.";
        if (!formData.keputusan_akhir)
            newErrors.keputusan_akhir = "Keputusan akhir wajib diisi.";

        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            toast.error("Harap lengkapi semua isian yang wajib.");
            return;
        }

        setIsSubmitting(true);
        try {
            const response = await storeKenaikanStudentData(
                siswaId,
                tahun,
                formData
            );
            toast.success(response.message);
            router.reload({ only: ["flash"] });
        } catch (err) {
            toast.error(err.response?.data?.message || "Gagal menyimpan data.");
        } finally {
            setIsSubmitting(false);
        }
    };

    return {
        initialData,
        isLoading,
        error,
        formData,
        isSubmitting,
        errors,
        handleFormChange,
        handleSubmit,
    };
};
