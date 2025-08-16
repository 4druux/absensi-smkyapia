import { useEffect } from "react";
import { usePage } from "@inertiajs/react";
import { toast } from "react-hot-toast";
import { School } from "lucide-react";

import PageContent from "@/Components/ui/page-content";
import CardContent from "@/Components/ui/card-content";
import DataNotFound from "@/Components/ui/data-not-found";
import DotLoader from "@/Components/ui/dot-loader";
import { useUserApproval } from "@/hooks/beranda/use-user-approval";
import { useBerandaClasses } from "@/hooks/beranda/use-beranda-classes";
import UserSection from "@/Components/beranda/user-section";

const HomePage = ({ auth, initialPendingUsers, initialApprovedUsers }) => {
    const breadcrumbItems = [{ label: "Beranda", href: route("home") }];

    const userApprovalProps = useUserApproval(
        initialPendingUsers,
        initialApprovedUsers
    );

    const { classes, isLoading, error } = useBerandaClasses();

    const { success } = usePage().props.flash;

    useEffect(() => {
        if (success) {
            toast.success(success);
        }
    }, [success]);

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-screen">
                <DotLoader text="Memuat daftar kelas..." />
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex items-center justify-center h-screen">
                Gagal memuat data kelas.
            </div>
        );
    }

    const groupedClasses = classes?.reduce((acc, kelas) => {
        (acc[kelas.kelas] = acc[kelas.kelas] || []).push(kelas);
        return acc;
    }, {});

    const classOrder = ["X", "XI", "XII"];
    const sortedGroupedClasses = {};
    classOrder.forEach((key) => {
        if (groupedClasses?.[key]) {
            sortedGroupedClasses[key] = groupedClasses[key];
        }
    });

    return (
        <>
            <PageContent
                breadcrumbItems={breadcrumbItems}
                pageClassName="-mt-20"
            >
                <h3 className="text-md md:text-lg font-medium text-neutral-700 mb-4 md:mb-6">
                    Pilih Kelas untuk Absensi dan Uang Kas
                </h3>
                {classes && classes.length > 0 ? (
                    <>
                        {Object.entries(sortedGroupedClasses).map(
                            ([namaKelas, kelasList]) => (
                                <div key={namaKelas} className="mb-8">
                                    <h4 className="text-md font-medium text-neutral-700 mb-4">{`Kelas ${namaKelas}`}</h4>
                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                        {kelasList
                                            .sort((a, b) =>
                                                a.kelompok.localeCompare(
                                                    b.kelompok
                                                )
                                            )
                                            .map((c) => (
                                                <CardContent
                                                    key={c.id}
                                                    href={route(
                                                        "beranda.class.show",
                                                        {
                                                            kelas: c.kelas,
                                                            jurusan: c.jurusan,
                                                        }
                                                    )}
                                                    icon={School}
                                                    title={`${c.kelas} ${c.kelompok}`}
                                                    subtitle={c.jurusan}
                                                />
                                            ))}
                                    </div>
                                </div>
                            )
                        )}
                    </>
                ) : (
                    <DataNotFound
                        title="Data Kelas Kosong"
                        message="Belum ada data kelas. Silakan tambahkan data siswa terlebih dahulu."
                    />
                )}
            </PageContent>

            {auth?.user?.role === "Super Admin" && (
                <UserSection {...userApprovalProps} />
            )}
        </>
    );
};

export default HomePage;
