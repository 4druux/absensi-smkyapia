import toast from "react-hot-toast";
import { router } from "@inertiajs/react";
import MainLayout from "@/Layouts/MainLayout";
import PageContent from "@/Components/common/PageContent";
import Button from "../../Components/common/Button";
import { School, PlusCircle, Trash2 } from "lucide-react";
import DataNotFound from "../../Components/common/DataNotFound";
import ContentCard from "../../Components/common/ContentCard";

const AllClasses = ({ classes }) => {
    const breadcrumbItems = [
        { label: "Data Siswa", href: route("data-siswa.index") },
        { label: "Daftar Kelas", href: null },
    ];

    const handleDeleteClass = (kelas, jurusan) => {
        if (
            confirm(
                `Apakah Anda yakin ingin menghapus kelas ${kelas} - ${jurusan} beserta semua data siswanya?`
            )
        ) {
            router.post(
                route("data-siswa.class.destroy", { kelas, jurusan }),
                {
                    _method: "delete",
                },
                {
                    onSuccess: () => {
                        toast.success(
                            `Kelas ${kelas} - ${jurusan} berhasil dihapus.`
                        );
                    },
                    onError: (errors) => {
                        toast.error("Gagal menghapus kelas.");
                        console.error("Errors:", errors);
                    },
                }
            );
        }
    };

    return (
        <PageContent breadcrumbItems={breadcrumbItems} pageClassName="-mt-20">
            <div className="flex justify-between items-center mb-6">
                <h3 className="text-md md:text-lg font-medium text-neutral-700">
                    Daftar Kelas
                </h3>

                <Button
                    as="link"
                    href={route("data-siswa.input")}
                    variant="primary"
                    size="sm"
                >
                    <PlusCircle className="w-5 h-5 mr-1" />
                    <span>Tambah Data </span>
                </Button>
            </div>
            {classes.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    {classes.map((c, index) => (
                        <ContentCard
                            key={index}
                            href={route("data-siswa.class.show", {
                                kelas: c.kelas,
                                jurusan: c.jurusan,
                            })}
                            icon={School}
                            title={c.kelas}
                            subtitle={c.jurusan}
                        >
                            <Button
                                size="sm"
                                variant="primary"
                                aria-label={`Hapus kelas ${c.kelas}-${c.jurusan}`}
                                className="absolute -top-4 -right-4 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200"
                                onClick={(e) => {
                                    e.preventDefault();
                                    handleDeleteClass(c.kelas, c.jurusan);
                                }}
                            >
                                <Trash2 size={16} />
                            </Button>
                        </ContentCard>
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

AllClasses.layout = (page) => (
    <MainLayout children={page} title="Daftar Kelas" />
);

export default AllClasses;
