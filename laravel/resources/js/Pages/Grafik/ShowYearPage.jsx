import { ArrowLeft, BookOpen, Loader2, School, Users } from "lucide-react";
import { FaFilePdf } from "react-icons/fa6";
import { RiFileExcel2Line } from "react-icons/ri";
import { IoStatsChart } from "react-icons/io5";

// Components
import BarChart from "@/Components/ui/bar-chart";
import ButtonRounded from "@/Components/common/button-rounded";
import DataNotFound from "@/Components/ui/data-not-found";
import DotLoader from "@/Components/ui/dot-loader";
import PageContent from "@/Components/ui/page-content";
import { useGrafikAbsensiYearlyData } from "@/hooks/grafik/use-grafik-absensi-yearly-data";

const ShowYearPage = ({ tahun, selectedClass }) => {
    const { chartData, isLoading, error, handleExport, downloadingStatus } =
        useGrafikAbsensiYearlyData(
            selectedClass.kelas,
            selectedClass.jurusan,
            tahun
        );

    const breadcrumbItems = [
        { label: "Grafik Absensi", href: route("grafik.index") },
        {
            label: `${selectedClass.kelas} ${selectedClass.kelompok} - ${selectedClass.jurusan}`,
            href: route("grafik.class.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
            }),
        },
        {
            label: tahun,
            href: route("grafik.class.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun: tahun,
            }),
        },
        { label: `Grafik Tahun ${tahun}`, href: null },
    ];

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-screen">
                <DotLoader text="Memuat data grafik..." />
            </div>
        );
    }

    if (error || !chartData) {
        return (
            <div className="flex items-center justify-center h-screen">
                Gagal memuat data grafik.
            </div>
        );
    }

    const isDataEmpty =
        !chartData ||
        (Array.isArray(chartData.telat) &&
            chartData.telat.every((val) => val === 0) &&
            chartData.alfa.every((val) => val === 0) &&
            chartData.sakit.every((val) => val === 0) &&
            chartData.izin.every((val) => val === 0) &&
            chartData.bolos.every((val) => val === 0));

    if (error || isDataEmpty) {
        return (
            <PageContent
                breadcrumbItems={breadcrumbItems}
                pageClassName="-mt-16 md:-mt-20"
            >
                <DataNotFound
                    title="Tidak Ada Data Absensi"
                    message={`Tidak ada data absensi untuk tahun ajaran ${tahun}.`}
                />
            </PageContent>
        );
    }

    const { labels, telat, alfa, sakit, izin, bolos } = chartData;

    return (
        <PageContent
            breadcrumbItems={breadcrumbItems}
            pageClassName="-mt-16 md:-mt-20"
        >
            <div className="flex flex-col md:flex-row md:justify-between md:items-center mb-4 md:mb-6 gap-6">
                <div className="flex items-center space-x-2 md:space-x-3">
                    <div className="p-3 rounded-lg bg-sky-100">
                        <IoStatsChart className="w-5 h-5 md:w-6 md:h-6 text-sky-600" />
                    </div>
                    <div>
                        <h3 className="text-md md:text-lg font-medium text-neutral-700">
                            Grafik Absensi Tahunan
                        </h3>
                        <div className="flex flex-row gap-2 md:mt-1 md:items-center">
                            <div className="flex items-center space-x-1 md:space-x-2 text-neutral-500">
                                <Users className="hidden w-5 h-5 md:block" />
                                <span className="text-xs md:text-sm">
                                    {selectedClass.jumlah_siswa} Siswa
                                </span>
                                <span className="block md:hidden">|</span>
                            </div>
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

            <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
                <div>
                    <BarChart
                        title="Total Keterlambatan per Bulan"
                        labels={labels}
                        data={telat}
                        backgroundColor="rgba(59, 130, 246, 0.6)"
                    />
                </div>
                <div>
                    <BarChart
                        title="Total Alfa per Bulan"
                        labels={labels}
                        data={alfa}
                        backgroundColor="rgba(239, 68, 68, 0.6)"
                    />
                </div>
                <div>
                    <BarChart
                        title="Total Sakit per Bulan"
                        labels={labels}
                        data={sakit}
                        backgroundColor="rgba(245, 158, 11, 0.6)"
                    />
                </div>
                <div>
                    <BarChart
                        title="Total Izin per Bulan"
                        labels={labels}
                        data={izin}
                        backgroundColor="rgba(107, 114, 128, 0.6)"
                    />
                </div>
                <div>
                    <BarChart
                        title="Total Bolos per Bulan"
                        labels={labels}
                        data={bolos}
                        backgroundColor="rgba(220, 38, 38, 0.6)"
                    />
                </div>
            </div>

            <div className="flex justify-start mt-8">
                <ButtonRounded
                    as="link"
                    variant="outline"
                    href={route("grafik.class.show", {
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



export default ShowYearPage;
