import { School } from "lucide-react";

// Components
import MainLayout from "@/Layouts/MainLayout";
import CardContent from "@/Components/ui/card-content";
import DataNotFound from "@/Components/ui/data-not-found";
import DotLoader from "@/Components/ui/dot-loader";
import PageContent from "@/Components/ui/page-content";
import { useAbsensiClasses } from "@/hooks/absensi/use-absensi-classes";

const SelectClassPage = () => {
    const { classes, isLoading, error } = useAbsensiClasses();

    const breadcrumbItems = [
        { label: "Absensi", href: route("absensi.index") },
        { label: "Pilih Kelas", href: null },
    ];

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

    return (
        <PageContent breadcrumbItems={breadcrumbItems} pageClassName="-mt-20">
            <h3 className="text-md md:text-lg font-medium text-neutral-700 mb-4 md:mb-6">
                Pilih Kelas
            </h3>
            {classes && classes.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    {classes.map((c, index) => (
                        <CardContent
                            key={c.id}
                            href={route("absensi.class.show", {
                                kelas: c.kelas,
                                jurusan: c.jurusan,
                            })}
                            icon={School}
                            title={`${c.kelas} ${c.kelompok}`}
                            subtitle={c.jurusan}
                        />
                    ))}
                </div>
            ) : (
                <DataNotFound
                    title="Data Kelas Kosong"
                    message="Belum ada data kelas. Silakan tambahkan data siswa terlebih dahulu."
                />
            )}
        </PageContent>
    );
};

SelectClassPage.layout = (page) => (
    <MainLayout children={page} title="Pilih Kelas Absensi" />
);

export default SelectClassPage;
