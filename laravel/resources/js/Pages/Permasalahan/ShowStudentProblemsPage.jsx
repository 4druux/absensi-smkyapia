import { useState } from "react";
import MainLayout from "@/Layouts/MainLayout";
import PageContent from "@/Components/ui/page-content";
import { ArrowLeft, BookOpen, PlusCircle, School, Users } from "lucide-react";
import Button from "@/Components/common/button";
import ProblemModal from "@/Components/permasalahan/problem-modal";
import { IoIosWarning } from "react-icons/io";
import toast from "react-hot-toast";
import { router } from "@inertiajs/react";
import { deleteStudentProblem } from "@/services/permasalahan/permasalahan-service";
import ProblemStudentTabel from "@/Components/permasalahan/problem-student-table";
import ProblemStudentCard from "@/Components/permasalahan/problem-student-card";

const ShowStudentProblemsPage = ({
    tahun,
    selectedClass,
    students,
    problems,
}) => {
    const [isModalOpen, setIsModalOpen] = useState(false);

    const handleDelete = async (id) => {
        if (confirm("Apakah Anda yakin ingin menghapus laporan ini?")) {
            try {
                const result = await deleteStudentProblem(id);
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
        { label: "Siswa", href: null },
    ];

    return (
        <>
            <ProblemModal
                isOpen={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                isStudentProblem={true}
                students={students}
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
                            Permasalahan Siswa
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
                    <Button
                        variant="primary"
                        size="sm"
                        onClick={() => setIsModalOpen(true)}
                    >
                        <PlusCircle className="w-4 h-4 mr-1 md:mr-2" />
                        <span className="text-xs md:text-sm font-medium">
                            Tambah Laporan
                        </span>
                    </Button>
                </div>

                <div className="hidden lg:block">
                    <ProblemStudentTabel
                        problems={problems}
                        handleDelete={handleDelete}
                    />
                </div>

                <div className="lg:hidden">
                    <ProblemStudentCard
                        problems={problems}
                        handleDelete={handleDelete}
                    />
                </div>

                <div className="flex justify-start mt-8">
                    <Button
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
                    </Button>
                </div>
            </PageContent>
        </>
    );
};

ShowStudentProblemsPage.layout = (page) => (
    <MainLayout
        children={page}
        title={`Permasalahan Siswa - ${page.props.tahun}`}
    />
);

export default ShowStudentProblemsPage;
