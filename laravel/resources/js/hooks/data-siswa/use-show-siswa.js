import { useState } from "react";
import useSWR from "swr";
import toast from "react-hot-toast";
import { fetcher } from '@/utils/api.js';
import { updateSiswa, deleteSiswa } from "@/services/data-siswa/siswa-service";

export const useShowSiswa = (classId) => {
    const swrKey = classId ? `/kelas/${classId}` : null;
    const {
        data: kelasData,
        error,
        isLoading,
        mutate,
    } = useSWR(swrKey, fetcher);

    const [editingId, setEditingId] = useState(null);
    const [editData, setEditData] = useState({});
    const [editErrors, setEditErrors] = useState({});

    const handleEditClick = (student) => {
        setEditingId(student.id);
        setEditData({
            nama: student.nama,
            nis: student.nis,
        });
        setEditErrors({});
    };

    const handleCancelEdit = () => {
        setEditingId(null);
        setEditData({});
        setEditErrors({});
    };

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setEditData((prev) => ({ ...prev, [name]: value }));
    };

    const handleUpdate = async (e, id) => {
        e.preventDefault();
        setEditErrors({});
        try {
            const result = await updateSiswa(id, editData);
            toast.success(result.message);
            setEditingId(null);
            mutate();
        } catch (error) {
            if (error.response?.status === 422) {
                const firstError = Object.values(
                    error.response.data.errors
                )[0][0];
                toast.error(firstError || "Gagal memperbarui data.");
                setEditErrors(error.response.data.errors);
            } else {
                toast.error(
                    "Gagal memperbarui data. Terjadi kesalahan server."
                );
            }
        }
    };

    const handleDelete = async (e, id) => {
        e.preventDefault();
        if (confirm("Apakah Anda yakin ingin menghapus siswa ini?")) {
            try {
                const result = await deleteSiswa(id);
                toast.success(result.message);
                mutate();
            } catch (error) {
                toast.error("Gagal menghapus siswa.");
            }
        }
    };

    return {
        students: kelasData?.siswas,
        isLoading,
        error,
        editingId,
        editData,
        editErrors,
        handleEditClick,
        handleCancelEdit,
        handleInputChange,
        handleUpdate,
        handleDelete,
    };
};
