import { ArrowLeft } from "lucide-react";

// Components
import MainLayout from "@/Layouts/MainLayout";
import Button from "@/Components/common/button";
import DataNotFound from "@/Components/ui/data-not-found";
import DotLoader from "@/Components/ui/dot-loader";
import PageContent from "@/Components/ui/page-content";
import ShowSiswaCard from "@/Components/data-siswa/show-siswa-card";
import ShowSiswaTable from "@/Components/data-siswa/show-siswa-table";
import { useShowSiswa } from "@/hooks/data-siswa/use-show-siswa";

const ShowClass = ({ selectedClass }) => {
    const {
        students,
        isLoading,
        error,
        editingId,
        editData,
        editErrors,
        handleEditClick,
        handleCancelEdit,
        handleInputChange,
        handleUpdate,
        handleDelete,
    } = useShowSiswa(selectedClass.id);

    const fullClassName = `${selectedClass.nama_kelas} ${selectedClass.kelompok} - ${selectedClass.jurusan.nama_jurusan}`;

    const breadcrumbItems = [
        { label: "Data Siswa", href: route("data-siswa.index") },
        { label: fullClassName, href: null },
    ];

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-screen">
                <DotLoader text="Memuat data siswa..." />
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex items-center justify-center h-screen">
                Gagal memuat data siswa.
            </div>
        );
    }

    const tableProps = {
        students,
        editingId,
        editData,
        editErrors,
        handleEditClick,
        handleCancelEdit,
        handleInputChange,
        handleUpdate,
        handleDelete,
    };

    return (
        <PageContent breadcrumbItems={breadcrumbItems} pageClassName="-mt-20">
            <div className="px-1 py-4">
                <h2 className="text-lg text-neutral-800">
                    Daftar Siswa Kelas {fullClassName}
                </h2>
            </div>
            {students && students.length > 0 ? (
                <>
                    <div className="hidden lg:block">
                        <ShowSiswaTable {...tableProps} />
                    </div>
                    <div className="lg:hidden">
                        <ShowSiswaCard {...tableProps} />
                    </div>
                </>
            ) : (
                <DataNotFound
                    title="Belum Ada Siswa"
                    message="Tidak ada data siswa yang ditemukan untuk kelas ini. Anda bisa menambahkannya di halaman Input Data."
                />
            )}
            <div className="mt-6 flex justify-start">
                <Button
                    as="link"
                    variant="outline"
                    href={route("data-siswa.index")}
                >
                    <ArrowLeft size={16} className="mr-2" />
                    Kembali
                </Button>
            </div>
        </PageContent>
    );
};

ShowClass.layout = (page) => (
    <MainLayout
        children={page}
        title={`Siswa Kelas ${page.props.selectedClass.nama_kelas} ${page.props.selectedClass.kelompok}`}
    />
);

export default ShowClass;
