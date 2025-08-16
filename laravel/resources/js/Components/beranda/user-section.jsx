import UserTable from "@/Components/beranda/user-table";
import UserCard from "@/Components/beranda/user-card";
import { Users } from "lucide-react";
import { FiUserCheck } from "react-icons/fi";
import { LiaUserClockSolid } from "react-icons/lia";

const UserSection = ({
    pendingUsers,
    approvedUsers,
    handleApprove,
    handleReject,
    isProcessing,
}) => {
    return (
        <div className="px-3 md:px-7 pb-10">
            <div className="bg-white shadow-lg rounded-2xl p-4 md:p-8 border border-slate-100">
                <div className="flex items-center space-x-2 md:space-x-3 mb-4">
                    <div className="p-3 rounded-lg bg-sky-100">
                        <Users className="w-5 h-5 md:w-6 md:h-6 text-sky-600" />
                    </div>
                    <div>
                        <h3 className="text-md md:text-lg font-medium text-neutral-700">
                            Data Pengguna
                        </h3>
                        <div className="flex flex-row gap-2 md:mt-1 md:items-center">
                            <div className="flex items-center space-x-1 md:space-x-2 text-neutral-600">
                                <LiaUserClockSolid className="w-4 h-4 md:w-5 md:h-5" />
                                <span className="text-xs md:text-sm">
                                    {pendingUsers.length}
                                </span>
                            </div>
                            <div className="flex items-center space-x-1 md:space-x-2 text-neutral-600">
                                <FiUserCheck className="w-4 h-4 md:w-5 md:h-5" />
                                <span className="text-xs md:text-sm">
                                    {approvedUsers.length}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {pendingUsers.length > 0 && (
                    <div className="mb-6">
                        <h3 className="text-md md:text-lg font-medium text-neutral-700 mb-2 md:mb-4">
                            Persetujuan Pengguna Baru
                        </h3>
                        <div className="hidden lg:block">
                            <UserTable
                                users={pendingUsers}
                                type="pending"
                                onApprove={handleApprove}
                                onReject={handleReject}
                                isProcessing={isProcessing}
                            />
                        </div>
                        <div className="lg:hidden">
                            <UserCard
                                users={pendingUsers}
                                type="pending"
                                onApprove={handleApprove}
                                onReject={handleReject}
                                isProcessing={isProcessing}
                            />
                        </div>
                    </div>
                )}

                {approvedUsers.length > 0 && (
                    <div className="mb-6">
                        <h3 className="text-md md:text-lg font-medium text-neutral-700 mb-2 md:mb-4">
                            Daftar Pengguna Aktif
                        </h3>
                        <div className="hidden lg:block">
                            <UserTable
                                users={approvedUsers}
                                type="approved"
                                onReject={handleReject}
                                isProcessing={isProcessing}
                            />
                        </div>
                        <div className="lg:hidden">
                            <UserCard
                                users={approvedUsers}
                                type="approved"
                                onReject={handleReject}
                                isProcessing={isProcessing}
                            />
                        </div>
                    </div>
                )}

                {pendingUsers.length === 0 && approvedUsers.length === 0 && (
                    <div className="text-center py-10 text-neutral-500 text-sm">
                        Tidak ada pendaftaran atau pengguna aktif.
                    </div>
                )}
            </div>
        </div>
    );
};

export default UserSection;
