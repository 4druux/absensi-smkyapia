import useSWR from "swr";
import toast from "react-hot-toast";
import { approveUser, rejectUser, getApprovedUsers, getPendingUsers } from "@/services/user/user-service";

export const useUserApproval = () => {
    const { data: pendingUsers, error: pendingError, isLoading: isPendingLoading, mutate: mutatePending } = useSWR('/users/pending', getPendingUsers);
    const { data: approvedUsers, error: approvedError, isLoading: isApprovedLoading, mutate: mutateApproved } = useSWR('/users/approved', getApprovedUsers);

    const isProcessing = false;

    const handleApprove = async (userId) => {
        try {
            await approveUser(userId);
            toast.success('Pengguna berhasil disetujui.');
            mutatePending();
            mutateApproved();
        } catch (err) {
            toast.error(err.response?.data?.message || 'Gagal menyetujui pengguna.');
        }
    };

    const handleReject = async (userId) => {
        if (confirm('Apakah Anda yakin ingin menolak dan menghapus pengguna ini?')) {
            try {
                await rejectUser(userId);
                toast.success('Pengguna berhasil ditolak dan dihapus.');
                mutatePending();
                mutateApproved();
            } catch (err) {
                toast.error(err.response?.data?.message || 'Gagal menolak pengguna.');
            }
        }
    };

    return {
        pendingUsers: pendingUsers || [],
        approvedUsers: approvedUsers || [],
        isProcessing,
        isLoading: isPendingLoading || isApprovedLoading,
        error: pendingError || approvedError,
        handleApprove,
        handleReject,
    };
};
