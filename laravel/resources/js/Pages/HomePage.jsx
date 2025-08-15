// HomePage
import { useEffect } from "react";
import { usePage } from "@inertiajs/react";
import { toast } from "react-hot-toast";

import PageContent from "@/Components/ui/page-content";
import DotLoader from "@/Components/ui/dot-loader";
import UserTable from "@/Components/user/user-table";
import UserCard from "@/Components/user/user-card";
import { useUserApproval } from "@/hooks/user/use-user-approval";
import { Users } from "lucide-react";
import { FaUserClock, FaUserCheck } from "react-icons/fa6";

const HomePage = ({ auth }) => {
    const breadcrumbItems = [{ label: "Beranda", href: route("home") }];

    const {
        pendingUsers,
        approvedUsers,
        isLoading,
        isProcessing,
        handleApprove,
        handleReject,
    } = useUserApproval();

    const { props } = usePage();
    const { success } = props.flash;

    useEffect(() => {
        if (success) {
            toast.success(success);
        }
    }, [success]);

    if (isLoading)
        return (
            <div className="flex items-center justify-center h-screen">
                <DotLoader />
            </div>
        );

    return (
        <>
            {auth?.user?.role === "Super Admin" && (
                <PageContent
                    breadcrumbItems={breadcrumbItems}
                    pageClassName="-mt-16 md:-mt-20"
                >
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
                                    <FaUserClock className="w-4 h-4 md:w-5 md:h-5" />
                                    <span className="text-xs md:text-sm">
                                        {pendingUsers.length}
                                    </span>
                                </div>

                                <div className="flex items-center space-x-1 md:space-x-2 text-neutral-600">
                                    <FaUserCheck className="w-4 h-4 md:w-5 md:h-5" />
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
                                Persetujuan Pendaftaran Pengguna Baru
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
                        <>
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
                        </>
                    )}

                    {pendingUsers.length === 0 &&
                        approvedUsers.length === 0 && (
                            <div className="text-center py-24 text-neutral-500 text-sm">
                                Tidak ada pendaftaran atau pengguna aktif.
                            </div>
                        )}
                </PageContent>
            )}
        </>
    );
};

export default HomePage;
