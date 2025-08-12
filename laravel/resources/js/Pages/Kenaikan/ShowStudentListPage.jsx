import { ArrowLeft, User, Loader2 } from "lucide-react";
import { FaFilePdf } from "react-icons/fa6";
import { RiFileExcel2Line } from "react-icons/ri";
import MainLayout from "@/Layouts/MainLayout";
import ButtonRounded from "@/Components/common/button-rounded";
import CardContent from "@/Components/ui/card-content";
import DataNotFound from "@/Components/ui/data-not-found";
import PageContent from "@/Components/ui/page-content";
import { useKenaikanExport } from "@/hooks/kenaikan/use-kenaikan-export";

const ShowStudentListPage = ({ tahun, selectedClass, students }) => {
    const { handleExport, downloadingStatus } = useKenaikanExport(
        selectedClass.kelas,
        selectedClass.jurusan,
        tahun
    );

    const breadcrumbItems = [
        {
            label: "Kenaikan",
            href: route("kenaikan-bersyarat.index"),
        },
        {
            label: `${selectedClass.kelas} ${selectedClass.kelompok} - ${selectedClass.jurusan}`,
            href: route("kenaikan-bersyarat.index"),
        },
        {
            label: tahun,
            href: route("kenaikan-bersyarat.class.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun: tahun,
            }),
        },
        { label: "Daftar Siswa", href: null },
    ];

    return (
        <PageContent
            breadcrumbItems={breadcrumbItems}
            pageClassName="-mt-16 md:-mt-20"
        >
            <div className="flex flex-col md:flex-row md:justify-between md:items-center mb-4 md:mb-6 gap-4">
                <h3 className="text-md md:text-lg font-medium text-neutral-700">
                    Pilih Siswa
                </h3>
                <div className="flex items-center justify-end gap-2">
                    <ButtonRounded
                        variant="outline"
                        size="sm"
                        onClick={() => handleExport("excel")}
                        disabled={downloadingStatus[`${tahun}-excel`]}
                    >
                        {downloadingStatus[`${tahun}-excel`] ? (
                            <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                        ) : (
                            <RiFileExcel2Line className="w-4 h-4 mr-2" />
                        )}
                        {downloadingStatus[`${tahun}-excel`]
                            ? "Mengekspor..."
                            : "Export Excel"}
                    </ButtonRounded>
                    <ButtonRounded
                        variant="primary"
                        size="sm"
                        onClick={() => handleExport("pdf")}
                        disabled={downloadingStatus[`${tahun}-pdf`]}
                    >
                        {downloadingStatus[`${tahun}-pdf`] ? (
                            <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                        ) : (
                            <FaFilePdf className="w-4 h-4 mr-2" />
                        )}
                        {downloadingStatus[`${tahun}-pdf`]
                            ? "Mengekspor..."
                            : "Export PDF"}
                    </ButtonRounded>
                </div>
            </div>

            {students && students.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    {students.map((student) => (
                        <CardContent
                            key={student.id}
                            href={route("kenaikan-bersyarat.student.show", {
                                kelas: selectedClass.kelas,
                                jurusan: selectedClass.jurusan,
                                tahun: tahun,
                                siswa: student.id,
                            })}
                            icon={User}
                            title={student.nama}
                            subtitle={`NIS: ${student.nis}`}
                            variant={
                                student.has_kenaikan_data
                                    ? "success"
                                    : "default"
                            }
                        />
                    ))}
                </div>
            ) : (
                <DataNotFound
                    title="Tidak Ada Siswa"
                    message="Tidak ditemukan data siswa di kelas ini."
                />
            )}

            <div className="flex justify-start mt-8">
                <ButtonRounded
                    as="link"
                    variant="outline"
                    href={route("kenaikan-bersyarat.class.show", {
                        kelas: selectedClass.kelas,
                        jurusan: selectedClass.jurusan,
                    })}
                >
                    <ArrowLeft size={16} className="mr-2" />
                    Kembali
                </ButtonRounded>
            </div>
        </PageContent>
    );
};

ShowStudentListPage.layout = (page) => (
    <MainLayout children={page} title={`Pilih Siswa - ${page.props.tahun}`} />
);

export default ShowStudentListPage;
