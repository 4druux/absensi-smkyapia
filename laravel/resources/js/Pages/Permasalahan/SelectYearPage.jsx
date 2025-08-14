import { useEffect } from "react";
import { usePage } from "@inertiajs/react";
import { ArrowLeft, CalendarDays, PlusCircle } from "lucide-react";
import toast from "react-hot-toast";

import ButtonRounded from "@/Components/common/button-rounded";
import CardContent from "@/Components/ui/card-content";
import DataNotFound from "@/Components/ui/data-not-found";
import DotLoader from "@/Components/ui/dot-loader";
import PageContent from "@/Components/ui/page-content";
import { usePermasalahanYears } from "@/hooks/permasalahan/use-permasalahan-years";

const SelectYearPage = ({ selectedClass, years: initialYears }) => {
    const { flash } = usePage().props;
    const {
        years = initialYears,
        isLoading,
        error,
        handleAddYear,
    } = usePermasalahanYears();

    useEffect(() => {
        if (flash && flash.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    const handleAddYearClick = (e) => {
        e.preventDefault();
        handleAddYear();
    };

    const breadcrumbItems = [
        {
            label: "Permasalahan",
            href: route("permasalahan.index"),
        },
        {
            label: `${selectedClass.kelas} ${selectedClass.kelompok} - ${selectedClass.jurusan}`,
            href: route("permasalahan.index"),
        },
        { label: "Pilih Tahun Ajaran", href: null },
    ];

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
                    Pilih Tahun Ajaran
                </h3>
                <ButtonRounded
                    onClick={handleAddYearClick}
                    disabled={isLoading}
                    variant="primary"
                    size="sm"
                >
                    <PlusCircle className="w-4 h-4 mr-1 md:mr-2" />
                    <span className="text-xs md:text-sm font-medium">
                        {isLoading ? "Memuat..." : "Tambah Tahun"}
                    </span>
                </ButtonRounded>
            </div>
            {years && years.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    {years.map((year, index) => (
                        <CardContent
                            key={index}
                            href={route("permasalahan.year.show", {
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
                <ButtonRounded
                    as="link"
                    variant="outline"
                    href={route("permasalahan.index")}
                >
                    <ArrowLeft size={16} className="mr-2" />
                    Kembali
                </ButtonRounded>
            </div>
        </PageContent>
    );
};

export default SelectYearPage;
