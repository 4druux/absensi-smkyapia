import { ArrowLeft, Users, UserX, Loader2 } from "lucide-react";
import { FaFilePdf } from "react-icons/fa6";
import { RiFileExcel2Line } from "react-icons/ri";
import ButtonRounded from "@/Components/common/button-rounded";
import CardContent from "@/Components/ui/card-content";
import PageContent from "@/Components/ui/page-content";
import { usePermasalahanExport } from "@/hooks/permasalahan/use-permasalahan-export";

const SelectProblemTypePage = ({ tahun, selectedClass }) => {
    const { handleExport, downloadingStatus } = usePermasalahanExport(
        selectedClass.kelas,
        selectedClass.jurusan,
        tahun
    );

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
            }),
        },
        { label: "Pilih Jenis Permasalahan", href: null },
    ];

    return (
        <PageContent
            breadcrumbItems={breadcrumbItems}
            pageClassName="-mt-16 md:-mt-20"
        >
            <div className="flex flex-col md:flex-row md:justify-between md:items-center mb-4 md:mb-6 gap-4">
                <h3 className="text-md md:text-lg font-medium text-neutral-700">
                    Pilih Jenis Laporan Permasalahan
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

            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                <CardContent
                    href={route("permasalahan.class-problems.show", {
                        kelas: selectedClass.kelas,
                        jurusan: selectedClass.jurusan,
                        tahun: tahun,
                    })}
                    icon={Users}
                    title="Permasalahan Kelas"
                    subtitle="Lihat dan kelola laporan permasalahan umum di kelas."
                />
                <CardContent
                    href={route("permasalahan.student-problems.show", {
                        kelas: selectedClass.kelas,
                        jurusan: selectedClass.jurusan,
                        tahun: tahun,
                    })}
                    icon={UserX}
                    title="Permasalahan Siswa"
                    subtitle="Lihat dan kelola laporan permasalahan spesifik per siswa."
                />
            </div>

            <div className="flex justify-start mt-8">
                <ButtonRounded
                    as="link"
                    variant="outline"
                    href={route("permasalahan.class.show", {
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

export default SelectProblemTypePage;
