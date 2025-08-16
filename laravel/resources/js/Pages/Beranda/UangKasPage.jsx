import { useEffect } from "react";
import { usePage } from "@inertiajs/react";
import { ArrowLeft } from "lucide-react";
import toast from "react-hot-toast";
import PageContent from "@/Components/ui/page-content";
import ButtonRounded from "@/Components/common/button-rounded";
import DataNotFound from "@/Components/ui/data-not-found";
import DotLoader from "@/Components/ui/dot-loader";
import UangKasHeader from "@/Components/uang-kas/uang-kas-header";
import UangKasTable from "@/Components/uang-kas/uang-kas-table";
import UangKasCard from "@/Components/uang-kas/uang-kas-card";
import { useBerandaWeeklyPayments } from "@/hooks/beranda/use-beranda-weekly-payments";
import { useBerandaOtherPayments } from "@/hooks/beranda/use-beranda-other-payments";
const UangKasPage = ({
    studentData,
    tahun,
    bulanSlug,
    namaBulan,
    selectedClass,
    minggu,
    iuranData,
}) => {
    const isWeeklyMode = !!minggu;

    const hookResult = isWeeklyMode
        ? useBerandaWeeklyPayments(
              selectedClass.kelas,
              selectedClass.jurusan,
              tahun,
              bulanSlug,
              minggu
          )
        : useBerandaOtherPayments(
              selectedClass.kelas,
              selectedClass.jurusan,
              iuranData.id
          );
    const { payments, fixedNominal, isLoading, error, dbSummary } = hookResult;

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
        return (
            <PageContent pageClassName="-mt-16 md:-mt-20">
                <DataNotFound
                    title="Data Siswa Kosong"
                    message={`Tidak ditemukan data siswa untuk kelas ${selectedClass.kelas} - ${selectedClass.jurusan}.`}
                />
            </PageContent>
        );
    }

    if (isLoading || !payments) {
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
        { label: "Beranda", href: route("home") },
        {
            label: `${selectedClass.kelas} - ${selectedClass.jurusan}`,
            href: route("beranda.class.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
            }),
        },
        {
            label: "Uang Kas",
            href: route("beranda.uang-kas.year.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
            }),
        },
        {
            label: isWeeklyMode ? tahun : iuranData?.tahun,
            href: route("beranda.uang-kas.month.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun: isWeeklyMode ? tahun : iuranData?.tahun,
            }),
        },
        {
            label: isWeeklyMode ? namaBulan : iuranData?.bulan,
            href: route("beranda.uang-kas.week.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun: isWeeklyMode ? tahun : iuranData?.tahun,
                bulanSlug: isWeeklyMode ? bulanSlug : iuranData?.bulanSlug,
            }),
        },
        {
            label: isWeeklyMode ? `Minggu ke-${minggu}` : iuranData?.deskripsi,
            href: null,
        },
    ];

    const backButtonHref = route("beranda.uang-kas.week.show", {
        kelas: selectedClass.kelas,
        jurusan: selectedClass.jurusan,
        tahun: isWeeklyMode ? tahun : iuranData?.tahun,
        bulanSlug: isWeeklyMode ? bulanSlug : iuranData?.bulanSlug,
    });

    return (
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
                isReadOnly={true}
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
                        isReadOnly={true}
                    />
                </div>
                <div className="lg:hidden">
                    <UangKasCard
                        students={studentData.students}
                        payments={payments}
                        isReadOnly={true}
                    />
                </div>
            </div>
            <div className="mt-6 flex items-center justify-end space-x-4">
                <ButtonRounded
                    as="link"
                    variant="outline"
                    href={backButtonHref}
                >
                    <ArrowLeft size={16} className="mr-2" />
                    Kembali
                </ButtonRounded>
            </div>
        </PageContent>
    );
};
export default UangKasPage;
