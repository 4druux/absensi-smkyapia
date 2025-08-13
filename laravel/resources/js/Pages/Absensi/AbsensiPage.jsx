import { useEffect } from "react";

import toast from "react-hot-toast";
import { usePage } from "@inertiajs/react";
import { Save, ArrowLeft } from "lucide-react";

// Components
import MainLayout from "@/Layouts/MainLayout";
import AbsensiTable from "@/Components/absensi/absensi-table";
import AbsensiCard from "@/Components/absensi/absensi-card";
import AbsensiHeader from "@/Components/absensi/absensi-header";
import ButtonRounded from "@/Components/common/button-rounded";
import DataNotFound from "@/Components/ui/data-not-found";
import DotLoader from "@/Components/ui/dot-loader";
import PageContent from "@/Components/ui/page-content";
import { useDailyAttendance } from "@/hooks/absensi/use-daily-attendance";

const AbsensiPage = ({ tanggal, bulan, namaBulan, tahun, selectedClass }) => {
    const {
        attendanceData,
        isLoading,
        error,
        isProcessing,
        hasAttendanceBeenSaved,
        attendance,
        attendanceStatuses,
        initialSummary,
        handleAttendanceChange,
        handleSubmit,
    } = useDailyAttendance(
        selectedClass.kelas,
        selectedClass.jurusan,
        tahun,
        bulan,
        tanggal
    );

    const { props } = usePage();
    useEffect(() => {
        if (props.flash?.success) toast.success(props.flash.success);
        if (props.flash?.error) toast.error(props.flash.error);
    }, [props.flash]);

    let displayYear;
    const [startYear, endYear] = tahun.split("-");
    const month = namaBulan.toLowerCase();

    if (
        month === "januari" ||
        month === "februari" ||
        month === "maret" ||
        month === "april" ||
        month === "mei" ||
        month === "juni"
    ) {
        displayYear = endYear;
    } else {
        displayYear = startYear;
    }

    if (
        !attendanceData ||
        !attendanceData.students ||
        attendanceData.students.length === 0
    ) {
        const notFoundBreadcrumb = [
            { label: "Absensi", href: route("absensi.index") },
            {
                label: `${selectedClass.kelas} - ${selectedClass.jurusan}`,
                href: route("absensi.index"),
            },
            {
                label: tahun,
                href: route("absensi.class.show", {
                    kelas: selectedClass.kelas,
                    jurusan: selectedClass.jurusan,
                }),
            },
            {
                label: `${namaBulan}`,
                href: route("absensi.year.show", {
                    kelas: selectedClass.kelas,
                    jurusan: selectedClass.jurusan,
                    tahun: tahun,
                }),
            },
            {
                label: `${tanggal}`,
                href: route("absensi.month.show", {
                    kelas: selectedClass.kelas,
                    jurusan: selectedClass.jurusan,
                    tahun: tahun,
                    bulanSlug: bulan,
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
                <DotLoader text="Memuat daftar siswa..." />
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex items-center justify-center h-screen">
                Gagal memuat data absensi.
            </div>
        );
    }

    const breadcrumbItems = [
        { label: "Absensi", href: route("absensi.index") },
        {
            label: `${selectedClass.kelas} ${selectedClass.kelompok} - ${selectedClass.jurusan}`,
            href: route("absensi.index"),
        },
        {
            label: tahun,
            href: route("absensi.class.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
            }),
        },
        {
            label: `${namaBulan}`,
            href: route("absensi.year.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun: tahun,
            }),
        },
        {
            label: `${tanggal}`,
            href: route("absensi.month.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun: tahun,
                bulanSlug: bulan,
            }),
        },
        { label: "Presensi Harian", href: null },
    ];

    const studentData = attendanceData;
    const tanggalAbsen = attendanceData.tanggalAbsen;
    const absenHeaderData = {
        studentData,
        tanggalAbsen,
        summary: initialSummary,
        selectedClass,
    };
    const absenDataProps = {
        students: studentData.students,
        attendance,
        onStatusChange: handleAttendanceChange,
        statuses: attendanceStatuses,
        isReadOnly: hasAttendanceBeenSaved,
    };

    return (
        <form onSubmit={handleSubmit}>
            <PageContent
                breadcrumbItems={breadcrumbItems}
                pageClassName="-mt-16 md:-mt-20"
            >
                <AbsensiHeader {...absenHeaderData} />

                <div>
                    <div className="px-1 py-4">
                        <h2 className="text-md md:text-lg text-neutral-800">
                            Daftar Kehadiran {tanggal} {namaBulan} {displayYear}
                        </h2>
                    </div>

                    <div className="hidden lg:block">
                        <AbsensiTable {...absenDataProps} />
                    </div>

                    <div className="lg:hidden">
                        <AbsensiCard {...absenDataProps} />
                    </div>
                </div>

                <div className="mt-6 flex items-center justify-end space-x-4">
                    <ButtonRounded
                        as="link"
                        variant="outline"
                        href={route("absensi.month.show", {
                            kelas: selectedClass.kelas,
                            jurusan: selectedClass.jurusan,
                            tahun,
                            bulanSlug: bulan,
                        })}
                    >
                        <ArrowLeft size={16} className="mr-2" />
                        Kembali
                    </ButtonRounded>

                    <ButtonRounded
                        type="submit"
                        variant="primary"
                        disabled={isProcessing}
                    >
                        <Save className="w-4 h-4 mr-2" />
                        {isProcessing ? "Menyimpan..." : "Simpan"}
                    </ButtonRounded>
                </div>
            </PageContent>
        </form>
    );
};

AbsensiPage.layout = (page) => (
    <MainLayout
        children={page}
        title={`Absensi ${page.props.tanggal} ${page.props.namaBulan}`}
    />
);

export default AbsensiPage;
