import { useState } from "react";

import { router } from "@inertiajs/react";
import { ArrowLeft, BookOpen, PlusCircle, School } from "lucide-react";
import { IoIosWarning } from "react-icons/io";
import toast from "react-hot-toast";

import ButtonRounded from "@/Components/common/button-rounded";
import ProblemClassCard from "@/Components/permasalahan/problem-class-card";
import ProblemClassTable from "@/Components/permasalahan/problem-class-table";
import ProblemModal from "@/Components/permasalahan/problem-modal";
import PageContent from "@/Components/ui/page-content";
import MainLayout from "@/Layouts/MainLayout";
import { deleteClassProblem } from "@/services/permasalahan/permasalahan-service";

const ShowClassProblemsPage = ({ tahun, selectedClass, problems }) => {
    const [isModalOpen, setIsModalOpen] = useState(false);

    const handleDelete = async (id) => {
        if (confirm("Apakah Anda yakin ingin menghapus laporan ini?")) {
            try {
                const result = await deleteClassProblem(id);
                toast.success(result.message);
                router.reload({ only: ["problems"] });
            } catch (err) {
                toast.error(
                    err.response?.data?.message || "Gagal menghapus data."
                );
            }
        }
    };

    const breadcrumbItems = [
        { label: "Permasalahan", href: route("permasalahan.index") },
        {
            label: `${selectedClass.kelas} ${selectedClass.kelompok} - ${selectedClass.jurusan}`,
            href: route("permasalahan.index"),
        },
        {
            label: tahun,
            href: route("permasalahan.class.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun: tahun,
            }),
        },
        {
            label: "Permasalahan",
            href: route("permasalahan.year.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun: tahun,
            }),
        },
        { label: "Kelas", href: null },
    ];

    return (
        <>
            <ProblemModal
                isOpen={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                isStudentProblem={false}
                selectedClass={selectedClass}
                tahun={tahun}
            />
            <PageContent
                breadcrumbItems={breadcrumbItems}
                pageClassName="-mt-16 md:-mt-20"
            >
                <div className="flex items-center space-x-2 md:space-x-3 mb-6">
                    <div className="p-3 rounded-lg bg-sky-100">
                        <IoIosWarning className="w-5 h-5 md:w-6 md:h-6 text-sky-600" />
                    </div>
                    <div>
                        <h3 className="text-md md:text-lg font-medium text-neutral-700">
                            Permasalahan Kelas
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
                        variant="primary"
                        size="sm"
                        onClick={() => setIsModalOpen(true)}
                    >
                        <PlusCircle className="w-4 h-4 mr-1 md:mr-2" />
                        <span className="text-xs md:text-sm font-medium">
                            Tambah Laporan
                        </span>
                    </ButtonRounded>
                </div>

                <div className="hidden lg:block">
                    <ProblemClassTable
                        problems={problems}
                        handleDelete={handleDelete}
                    />
                </div>

                <div className="lg:hidden">
                    <ProblemClassCard
                        problems={problems}
                        handleDelete={handleDelete}
                    />
                </div>

                <div className="flex justify-start mt-8">
                    <ButtonRounded
                        as="link"
                        variant="outline"
                        href={route("permasalahan.year.show", {
                            kelas: selectedClass.kelas,
                            jurusan: selectedClass.jurusan,
                            tahun: tahun,
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

ShowClassProblemsPage.layout = (page) => (
    <MainLayout
        children={page}
        title={`Permasalahan Kelas - ${page.props.tahun}`}
    />
);

export default ShowClassProblemsPage;
