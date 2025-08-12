import { School, PlusCircle, Trash2 } from "lucide-react";

// Components
import MainLayout from "@/Layouts/MainLayout";
import ButtonRounded from "@/Components/common/button-rounded";
import CardContent from "@/Components/ui/card-content";
import DataNotFound from "@/Components/ui/data-not-found";
import DotLoader from "@/Components/ui/dot-loader";
import PageContent from "@/Components/ui/page-content";
import { useKelas } from "@/hooks/data-siswa/use-kelas";

const AllClasses = () => {
    const { allKelas, isLoading, error, handleDeleteKelas } = useKelas();

    const breadcrumbItems = [
        { label: "Data Siswa", href: route("data-siswa.index") },
        { label: "Daftar Kelas", href: null },
    ];

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-screen">
                <DotLoader text="Memuat daftar kelas..." />
            </div>
        );
    }

    if (error)
        return (
            <div className="flex items-center justify-center h-screen">
                Gagal memuat data kelas.
            </div>
        );

    const groupedClasses = allKelas?.reduce((acc, kelas) => {
        (acc[kelas.nama_kelas] = acc[kelas.nama_kelas] || []).push(kelas);
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
            <div className="flex justify-between items-center mb-6">
                <h3 className="text-md md:text-lg font-medium text-neutral-700">
                    Daftar Kelas
                </h3>
                <ButtonRounded
                    as="link"
                    href={route("data-siswa.input")}
                    variant="primary"
                    size="sm"
                >
                    <PlusCircle className="w-5 h-5 mr-1" />
                    <span>Tambah Data</span>
                </ButtonRounded>
            </div>

            {allKelas && allKelas.length > 0 ? (
                <>
                    {Object.entries(sortedGroupedClasses).map(
                        ([namaKelas, kelasList]) => (
                            <div key={namaKelas} className="mb-8">
                                <h4 className="text-md font-medium text-neutral-700 mb-4">{`Kelas ${namaKelas}`}</h4>
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                    {kelasList.map((kelas) => (
                                        <CardContent
                                            key={kelas.id}
                                            href={route(
                                                "data-siswa.class.show",
                                                {
                                                    kelas: kelas.id,
                                                }
                                            )}
                                            icon={School}
                                            title={`${kelas.nama_kelas} ${kelas.kelompok}`}
                                            subtitle={
                                                kelas.jurusan?.nama_jurusan
                                            }
                                        >
                                            <ButtonRounded
                                                size="sm"
                                                variant="primary"
                                                aria-label={`Hapus kelas ${kelas.nama_kelas} ${kelas.kelompok}`}
                                                className="absolute -top-4 -right-4 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200"
                                                onClick={(e) => {
                                                    e.preventDefault();
                                                    const className = `${kelas.nama_kelas} ${kelas.kelompok} - ${kelas.jurusan?.nama_jurusan}`;
                                                    handleDeleteKelas(
                                                        kelas.id,
                                                        className
                                                    );
                                                }}
                                            >
                                                <Trash2 size={16} />
                                            </ButtonRounded>
                                        </CardContent>
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

AllClasses.layout = (page) => (
    <MainLayout children={page} title="Daftar Kelas" />
);

export default AllClasses;
