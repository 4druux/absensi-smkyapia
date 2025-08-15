import useSWR, { mutate } from "swr";
import toast from "react-hot-toast";
import {
    approveUser,
    rejectUser,
    getApprovedUsers,
    getPendingUsers,
} from "@/services/user/user-service";

export const useUserApproval = (initialPending, initialApproved) => {
    const SWR_KEY_PENDING = "/users/pending";
    const SWR_KEY_APPROVED = "/users/approved";

    const {
        data: pendingData,
        error: pendingError,
        isLoading: isPendingLoading,
    } = useSWR(SWR_KEY_PENDING, getPendingUsers, {
        fallbackData: initialPending,
    });

    const {
        data: approvedData,
        error: approvedError,
        isLoading: isApprovedLoading,
    } = useSWR(SWR_KEY_APPROVED, getApprovedUsers, {
        fallbackData: initialApproved,
    });

    const handleApprove = async (userId) => {
        try {
            await approveUser(userId);
            toast.success("Pengguna berhasil disetujui.");
            mutate(SWR_KEY_PENDING);
            mutate(SWR_KEY_APPROVED);
        } catch (err) {
            toast.error(
                err.response?.data?.message || "Gagal menyetujui pengguna."
            );
        }
    };

    const handleReject = async (userId) => {
        if (
            confirm(
                "Apakah Anda yakin ingin menolak dan menghapus pengguna ini?"
            )
        ) {
            try {
                await rejectUser(userId);
                toast.success("Pengguna berhasil ditolak dan dihapus.");
                mutate(SWR_KEY_PENDING);
                mutate(SWR_KEY_APPROVED);
            } catch (err) {
                toast.error(
                    err.response?.data?.message || "Gagal menolak pengguna."
                );
            }
        }
    };

    return {
        pendingUsers: pendingData || [],
        approvedUsers: approvedData || [],
        isProcessing: false,
        isLoading: isPendingLoading || isApprovedLoading,
        error: pendingError || approvedError,
        handleApprove,
        handleReject,
    };
};
