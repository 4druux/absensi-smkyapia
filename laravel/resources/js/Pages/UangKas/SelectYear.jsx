import { useState } from "react";
import { router } from "@inertiajs/react";
import MainLayout from "@/Layouts/MainLayout";
import { ArrowLeft, CalendarDays, PlusCircle } from "lucide-react";
import PageContent from "@/Components/common/PageContent";
import ContentCard from "@/Components/common/ContentCard";
import Button from "@/Components/common/Button";
import DataNotFound from "@/Components/common/DataNotFound";
import toast from "react-hot-toast";

const SelectYear = ({ academicYears, selectedClass }) => {
    const [processing, setProcessing] = useState(false);

    const breadcrumbItems = [
        { label: "Uang Kas", href: route("uang-kas.index") },
        {
            label: `${selectedClass.kelas} - ${selectedClass.jurusan}`,
            href: route("uang-kas.index"),
        },
        { label: "Pilih Tahun", href: null },
    ];

    const handleAddYear = (e) => {
        e.preventDefault();
        setProcessing(true);

        router.post(
            route("uang-kas.year.store"),
            {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
            },
            {
                onSuccess: () => {
                    toast.success("Tahun ajaran berhasil ditambahkan!");
                },
                onError: (errors) => {
                    if (errors.year) {
                        toast.error(errors.year);
                    } else {
                        toast.error(
                            "Gagal menambahkan tahun. Terjadi kesalahan."
                        );
                    }
                },
                onFinish: () => {
                    setProcessing(false);
                },
            }
        );
    };

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
                    onClick={handleAddYear}
                    disabled={processing}
                    variant="primary"
                    size="sm"
                >
                    <PlusCircle className="w-4 h-4 mr-2" />
                    <span>
                        {processing ? "Menambahkan..." : "Tambah Tahun"}
                    </span>
                </Button>
            </div>

            {academicYears.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    {academicYears.map((year) => (
                        <ContentCard
                            key={year.id}
                            href={route("uang-kas.year.show", {
                                kelas: selectedClass.kelas,
                                jurusan: selectedClass.jurusan,
                                tahun: year.year,
                            })}
                            icon={CalendarDays}
                            title={year.year}
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
                    href={route("uang-kas.index")}
                >
                    <ArrowLeft size={16} className="mr-2" />
                    Kembali
                </Button>
            </div>
        </PageContent>
    );
};

SelectYear.layout = (page) => (
    <MainLayout
        children={page}
        title={`Pilih Tahun - ${page.props.selectedClass.kelas} ${page.props.selectedClass.jurusan}`}
    />
);

export default SelectYear;
