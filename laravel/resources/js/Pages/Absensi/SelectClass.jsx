import MainLayout from "@/Layouts/MainLayout";
import PageContent from "@/Components/common/PageContent";
import ContentCard from "@/Components/common/ContentCard";
import DataNotFound from "@/Components/common/DataNotFound";
import { School } from "lucide-react";

const SelectClass = ({ classes }) => {
    const breadcrumbItems = [
        { label: "Absensi", href: route("absensi.index") },
        { label: "Pilih Kelas", href: null },
    ];

    return (
        <PageContent breadcrumbItems={breadcrumbItems} pageClassName="-mt-20">
            <h3 className="text-md md:text-lg font-medium text-neutral-700 mb-4 md:mb-6">
                Pilih Kelas
            </h3>
            {classes.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    {classes.map((c, index) => (
                        <ContentCard
                            key={index}
                            href={route("absensi.class.show", {
                                kelas: c.kelas,
                                jurusan: c.jurusan,
                            })}
                            icon={School}
                            title={c.kelas}
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

SelectClass.layout = (page) => (
    <MainLayout children={page} title="Pilih Kelas Absensi" />
);

export default SelectClass;
