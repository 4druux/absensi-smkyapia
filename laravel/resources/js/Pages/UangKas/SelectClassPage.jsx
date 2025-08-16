import PageContent from "@/Components/ui/page-content";
import CardContent from "@/Components/ui/card-content";
import DataNotFound from "@/Components/ui/data-not-found";
import DotLoader from "@/Components/ui/dot-loader";
import { School } from "lucide-react";
import { useUangKasClasses } from "@/hooks/uang-kas/use-uang-kas-classes";

const SelectClassPage = () => {
    const { classes, isLoading, error } = useUangKasClasses();

    const breadcrumbItems = [
        { label: "Uang Kas", href: route("uang-kas.index") },
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
        <PageContent breadcrumbItems={breadcrumbItems} pageClassName="-mt-20">
            <h3 className="text-md md:text-lg font-medium text-neutral-700 mb-4 md:mb-6">
                Pilih Kelas Untuk Uang Kas
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
                                            a.kelompok.localeCompare(b.kelompok)
                                        )
                                        .map((c) => (
                                            <CardContent
                                                key={c.id}
                                                href={route(
                                                    "uang-kas.class.show",
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
    );
};

export default SelectClassPage;
