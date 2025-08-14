import { useState } from "react";
import { ArrowLeft, BookOpen, PlusCircle, School } from "lucide-react";
import { IoIosWarning } from "react-icons/io";
import toast from "react-hot-toast";

// Components
import PageContent from "@/Components/ui/page-content";
import ButtonRounded from "@/Components/common/button-rounded";
import DotLoader from "@/Components/ui/dot-loader";
import IndisiplinerTable from "@/Components/indisipliner/indisipliner-table";
import IndisiplinerCard from "@/Components/indisipliner/indisipliner-card";
import IndisiplinerModal from "@/Components/indisipliner/indisipliner-modal";
import { useIndisiplinerData } from "@/hooks/indisipliner/use-indisipliner-data.js";
import { deleteIndisipliner } from "@/services/indisipliner/indisipliner-service";

const ShowIndisiplinerPage = ({ selectedClass, students, tahun }) => {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const { indisiplinerData, isLoading, error, mutate, handleExportStudent } =
        useIndisiplinerData(selectedClass.id, tahun);

    const openModal = () => setIsModalOpen(true);
    const closeModal = () => setIsModalOpen(false);

    const sortedIndisiplinerData = indisiplinerData
        ? [...indisiplinerData].sort(
              (a, b) => new Date(a.tanggal_surat) - new Date(b.tanggal_surat)
          )
        : [];

    const handleDelete = async (id) => {
        if (
            confirm("Apakah Anda yakin ingin menghapus data indisipliner ini?")
        ) {
            try {
                await deleteIndisipliner(id);
                toast.success("Data Indisipliner berhasil dihapus!");
                mutate();
            } catch (err) {
                toast.error("Gagal menghapus data."), err.response.data.errors;
            }
        }
    };

    const breadcrumbItems = [
        { label: "Data Indisipliner", href: route("indisipliner.index") },
        {
            label: `${selectedClass.kelas} ${selectedClass.kelompok} - ${selectedClass.jurusan}`,
            href: route("indisipliner.index"),
        },

        {
            label: `${tahun}`,
            href: route("indisipliner.class.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun: tahun,
            }),
        },
        { label: "Data Indisipliner", href: null },
    ];

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-screen">
                <DotLoader text="Memuat data indisipliner..." />
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex items-center justify-center h-screen">
                Gagal memuat data indisipliner.
            </div>
        );
    }

    return (
        <>
            <IndisiplinerModal
                isOpen={isModalOpen}
                onClose={closeModal}
                students={students}
                selectedClass={selectedClass}
                tahun={tahun}
                mutate={mutate}
            />
            <PageContent
                breadcrumbItems={breadcrumbItems}
                pageClassName="-mt-16 md:-mt-20"
            >
                <div className="flex items-center space-x-2 md:space-x-3 mb-4">
                    <div className="p-3 rounded-lg bg-sky-100">
                        <IoIosWarning className="w-5 h-5 md:w-6 md:h-6 text-sky-600" />
                    </div>
                    <div>
                        <h3 className="text-md md:text-lg font-medium text-neutral-700">
                            Data Indisipliner
                        </h3>
                        <div className="flex flex-row gap-2 md:mt-1 md:items-center">
                            <div className="flex items-center space-x-1 md:space-x-2 text-neutral-500">
                                <School className="hidden w-5 h-5 md:block" />
                                <span className="text-xs md:text-sm">
                                    {selectedClass.kelas}{" "}
                                    {selectedClass.kelompok}
                                </span>
                                <span className="block md:hidden">|</span>
                            </div>
                            <div className="flex items-center space-x-1 md:space-x-2 text-neutral-500">
                                <BookOpen className="hidden w-5 h-5 md:block" />
                                <span className="text-xs md:text-sm">
                                    {selectedClass.jurusan}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="flex items-center justify-end mb-6">
                    <ButtonRounded
                        onClick={openModal}
                        disabled={isLoading}
                        variant="primary"
                        size="sm"
                    >
                        <PlusCircle className="w-4 h-4 mr-1 md:mr-2" />
                        <span className="text-xs md:text-sm font-medium">
                            Tambah Data
                        </span>
                    </ButtonRounded>
                </div>

                <div className="hidden lg:block">
                    <IndisiplinerTable
                        indisiplinerData={sortedIndisiplinerData}
                        handleDelete={handleDelete}
                        handleExportStudent={handleExportStudent}
                    />
                </div>

                <div className="lg:hidden">
                    <IndisiplinerCard
                        indisiplinerData={sortedIndisiplinerData}
                        handleDelete={handleDelete}
                        handleExportStudent={handleExportStudent}
                    />
                </div>

                <div className="flex justify-start mt-8">
                    <ButtonRounded
                        as="link"
                        variant="outline"
                        href={route("indisipliner.class.show", {
                            kelas: selectedClass.kelas,
                            jurusan: selectedClass.jurusan,
                        })}
                    >
                        <ArrowLeft size={16} className="mr-2" />
                        Kembali
                    </ButtonRounded>
                </div>
            </PageContent>
        </>
    );
};

export default ShowIndisiplinerPage;
