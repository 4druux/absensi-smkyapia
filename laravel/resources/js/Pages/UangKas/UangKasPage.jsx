// UangKas/UangKasPage.jsx
import { useEffect } from "react";
import { usePage } from "@inertiajs/react";
import { Save, ArrowLeft } from "lucide-react";
import toast from "react-hot-toast";

// Components
import MainLayout from "@/Layouts/MainLayout";
import PageContent from "@/Components/ui/page-content";
import Button from "@/Components/common/button";
import DataNotFound from "@/Components/ui/data-not-found";
import DotLoader from "@/Components/ui/dot-loader";
import UangKasHeader from "@/Components/uang-kas/uang-kas-header";
import UangKasTable from "@/Components/uang-kas/uang-kas-table";
import UangKasCard from "@/Components/uang-kas/uang-kas-card";
import { useWeeklyPayments } from "@/hooks/uang-kas/use-weekly-payments";

const UangKasPage = ({
    studentData,
    tahun,
    bulanSlug,
    minggu,
    existingPayments,
    selectedClass,
    namaBulan,
}) => {
    const {
        payments,
        fixedNominal,
        isProcessing,
        isLoading,
        error,
        handlePaymentChange,
        handleNominalChange,
        handleSelectAllChange,
        handleSubmit,
        dbSummary,
        hasChanges,
        hasAnyPaymentBeenSaved,
        allStudentsPaidFromDb,
    } = useWeeklyPayments(
        selectedClass.kelas,
        selectedClass.jurusan,
        tahun,
        bulanSlug,
        minggu
    );

    const { props } = usePage();
    useEffect(() => {
        if (props.flash?.success) toast.success(props.flash.success);
        if (props.flash?.error) toast.error(props.flash.error);
    }, [props.flash]);

    if (
        !studentData ||
        !studentData.students ||
        studentData.students.length === 0
    ) {
        const notFoundBreadcrumb = [
            { label: "Uang Kas", href: route("uang-kas.index") },
            {
                label: `${selectedClass.kelas} - ${selectedClass.jurusan}`,
                href: route("uang-kas.index"),
            },
            {
                label: tahun,
                href: route("uang-kas.class.show", {
                    kelas: selectedClass.kelas,
                    jurusan: selectedClass.jurusan,
                    tahun: tahun,
                }),
            },
            {
                label: namaBulan,
                href: route("uang-kas.year.show", {
                    kelas: selectedClass.kelas,
                    jurusan: selectedClass.jurusan,
                    tahun: tahun,
                    bulanSlug: bulanSlug,
                }),
            },
            {
                label: `Minggu ${minggu}`,
                href: route("uang-kas.month.show", {
                    kelas: selectedClass.kelas,
                    jurusan: selectedClass.jurusan,
                    tahun: tahun,
                    bulanSlug: bulanSlug,
                }),
            },
            { label: "Data Tidak Ditemukan", href: null },
        ];

        return (
            <PageContent
                breadcrumbItems={notFoundBreadcrumb}
                pageClassName="-mt-16 md:-mt-20"
            >
                <DataNotFound
                    title="Data Siswa Kosong"
                    message={`Tidak ditemukan data siswa untuk kelas ${selectedClass.kelas} - ${selectedClass.jurusan}. Silakan input data siswa terlebih dahulu.`}
                />
            </PageContent>
        );
    }

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-screen">
                <DotLoader text="Memuat data pembayaran..." />
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex items-center justify-center h-screen">
                Gagal memuat data pembayaran.
            </div>
        );
    }

    const breadcrumbItems = [
        { label: "Uang Kas", href: route("uang-kas.index") },
        {
            label: `${selectedClass.kelas} - ${selectedClass.jurusan}`,
            href: route("uang-kas.index"),
        },
        {
            label: tahun,
            href: route("uang-kas.class.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun: tahun,
            }),
        },
        {
            label: namaBulan,
            href: route("uang-kas.year.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun: tahun,
                bulanSlug: bulanSlug,
            }),
        },
        {
            label: `${minggu}`,
            href: route("uang-kas.month.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun: tahun,
                bulanSlug: bulanSlug,
            }),
        },
        { label: "Kas Mingguan", href: null },
    ];

    return (
        <form onSubmit={handleSubmit}>
            <PageContent
                breadcrumbItems={breadcrumbItems}
                pageClassName="-mt-16 md:-mt-20"
            >
                <UangKasHeader
                    studentData={{
                        ...studentData,
                        classCode: selectedClass.kelas,
                        major: selectedClass.jurusan,
                    }}
                    summary={dbSummary}
                    nominal={fixedNominal}
                    onNominalChange={handleNominalChange}
                    isReadOnly={hasAnyPaymentBeenSaved}
                />

                <div>
                    <div className="px-1 py-4">
                        <h2 className="text-md md:text-lg text-neutral-800">
                            Daftar Pembayaran
                        </h2>
                    </div>

                    <div className="hidden lg:block">
                        <UangKasTable
                            students={studentData.students}
                            payments={payments}
                            existingPayments={existingPayments}
                            onPaymentChange={handlePaymentChange}
                            allStudentsPaidFromDb={allStudentsPaidFromDb}
                            onSelectAllChange={handleSelectAllChange}
                        />
                    </div>

                    <div className="lg:hidden">
                        <UangKasCard
                            students={studentData.students}
                            payments={payments}
                            existingPayments={existingPayments}
                            onPaymentChange={handlePaymentChange}
                            allStudentsPaidFromDb={allStudentsPaidFromDb}
                            onSelectAllChange={handleSelectAllChange}
                        />
                    </div>
                </div>

                <div className="mt-6 flex items-center justify-end space-x-4">
                    <Button
                        as="link"
                        variant="outline"
                        href={route("uang-kas.month.show", {
                            kelas: selectedClass.kelas,
                            jurusan: selectedClass.jurusan,
                            tahun,
                            bulanSlug,
                        })}
                    >
                        <ArrowLeft size={16} className="mr-2" />
                        Kembali
                    </Button>

                    <Button
                        type="submit"
                        variant="primary"
                        disabled={isProcessing || !hasChanges()}
                    >
                        <Save className="w-4 h-4 mr-2" />
                        {isProcessing
                            ? "Menyimpan..."
                            : !hasChanges()
                            ? "Disimpan"
                            : "Simpan"}
                    </Button>
                </div>
            </PageContent>
        </form>
    );
};

UangKasPage.layout = (page) => (
    <MainLayout
        children={page}
        title={`Uang Kas ${page.props.selectedClass.kelas} - Minggu ${page.props.minggu}`}
    />
);

export default UangKasPage;
