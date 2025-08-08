import { CalendarDays, ArrowLeft, PlusCircle } from "lucide-react";

// Components
import MainLayout from "@/Layouts/MainLayout";
import Button from "@/Components/common/button";
import CardContent from "@/Components/ui/card-content";
import DataNotFound from "@/Components/ui/data-not-found";
import DotLoader from "@/Components/ui/dot-loader";
import PageContent from "@/Components/ui/page-content";
import { useAbsensiYears } from "@/hooks/absensi/use-absensi-years";

const SelectYearPage = ({ selectedClass }) => {
    const { years, isLoading, error, handleAddYear } = useAbsensiYears();

    const breadcrumbItems = [
        { label: "Absensi", href: route("absensi.index") },
        {
            label: `${selectedClass.kelas} - ${selectedClass.jurusan}`,
            href: route("absensi.index"),
        },
        { label: "Pilih Tahun", href: null },
    ];

    const handleAddYearClick = (e) => {
        e.preventDefault();
        handleAddYear();
    };

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-screen">
                <DotLoader text="Memuat daftar tahun..." />
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex items-center justify-center h-screen">
                Gagal memuat data tahun.
            </div>
        );
    }

    return (
        <PageContent
            breadcrumbItems={breadcrumbItems}
            pageClassName="-mt-16 md:-mt-20"
        >
            <div className="flex justify-between items-center mb-6">
                <h3 className="text-md md:text-lg font-medium text-neutral-700">
                    Pilih Tahun
                </h3>
                <Button
                    onClick={handleAddYearClick}
                    disabled={isLoading}
                    variant="primary"
                    size="sm"
                >
                    <PlusCircle className="w-4 h-4 mr-2" />
                    <span>{isLoading ? "Memuat..." : "Tambah Tahun"}</span>
                </Button>
            </div>
            {years && years.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    {years.map((year, index) => (
                        <CardContent
                            key={index}
                            href={route("absensi.year.show", {
                                kelas: selectedClass.kelas,
                                jurusan: selectedClass.jurusan,
                                tahun: year.nomor,
                            })}
                            icon={CalendarDays}
                            title={year.nomor}
                        />
                    ))}
                </div>
            ) : (
                <DataNotFound
                    title="Data Tahun Ajaran Kosong"
                    message="Tidak ditemukan data tahun ajaran. Silakan tambahkan tahun ajaran terlebih dahulu."
                />
            )}

            <div className="flex justify-start mt-8">
                <Button
                    as="link"
                    variant="outline"
                    href={route("absensi.index")}
                >
                    <ArrowLeft size={16} className="mr-2" />
                    Kembali
                </Button>
            </div>
        </PageContent>
    );
};

SelectYearPage.layout = (page) => (
    <MainLayout children={page} title="Pilih Tahun" />
);

export default SelectYearPage;
