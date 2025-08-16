import { useEffect } from "react";
import toast from "react-hot-toast";
import { usePage } from "@inertiajs/react";
import { ArrowLeft } from "lucide-react";
import BerandaAbsensiTable from "@/Components/beranda/beranda-absensi-table";
import BerandaAbsensiCard from "@/Components/beranda/beranda-absensi-card";
import BerandaAbsensiHeader from "@/Components/beranda/beranda-absensi-header";
import ButtonRounded from "@/Components/common/button-rounded";
import DataNotFound from "@/Components/ui/data-not-found";
import DotLoader from "@/Components/ui/dot-loader";
import PageContent from "@/Components/ui/page-content";
import { useBerandaDailyAttendance } from "@/hooks/beranda/use-beranda-daily-attendance";
const AbsensiPage = ({
    tanggal,
    bulanSlug,
    namaBulan,
    tahun,
    selectedClass,
}) => {
    const { attendanceData, isLoading, error, summary, attendanceStatuses } =
        useBerandaDailyAttendance(
            selectedClass.kelas,
            selectedClass.jurusan,
            tahun,
            bulanSlug,
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
            { label: "Beranda", href: route("home") },
            {
                label: `${selectedClass.kelas} - ${selectedClass.jurusan}`,
                href: route("beranda.class.show", {
                    kelas: selectedClass.kelas,
                    jurusan: selectedClass.jurusan,
                }),
            },
            {
                label: "Absensi",
                href: route("beranda.absensi.year.show", {
                    kelas: selectedClass.kelas,
                    jurusan: selectedClass.jurusan,
                }),
            },
            {
                label: tahun,
                href: route("beranda.absensi.month.show", {
                    kelas: selectedClass.kelas,
                    jurusan: selectedClass.jurusan,
                    tahun: tahun,
                }),
            },
            {
                label: `${namaBulan}`,
                href: route("beranda.absensi.day.show", {
                    kelas: selectedClass.kelas,
                    jurusan: selectedClass.jurusan,
                    tahun: tahun,
                    bulanSlug: bulanSlug,
                }),
            },
            { label: `${tanggal}`, href: null },
            { label: "Data Tidak Ditemukan", href: null },
        ];
        return (
            <PageContent
                breadcrumbItems={notFoundBreadcrumb}
                pageClassName="-mt-16 md:-mt-20"
            >
                <DataNotFound
                    title="Data Siswa Kosong"
                    message={`Tidak ditemukan data siswa untuk kelas ${selectedClass.kelas} - ${selectedClass.jurusan}.`}
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
        { label: "Beranda", href: route("home") },
        {
            label: `${selectedClass.kelas} ${selectedClass.kelompok} - ${selectedClass.jurusan}`,
            href: route("home"),
        },
        {
            label: "Absensi",
            href: route("beranda.class.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
            }),
        },
        {
            label: tahun,
            href: route("beranda.absensi.year.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
            }),
        },
        {
            label: `${namaBulan}`,
            href: route("beranda.absensi.month.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun: tahun,
            }),
        },
        {
            label: `${tanggal}`,
            href: route("beranda.absensi.day.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun: tahun,
                bulanSlug: bulanSlug,
            }),
        },
        { label: "Presensi Harian", href: null },
    ];
    const absenHeaderData = {
        studentData: attendanceData,
        tanggalAbsen: attendanceData.tanggalAbsen,
        summary: summary,
        selectedClass,
    };
    const absenDataProps = {
        students: attendanceData.students,
        attendance: attendanceData.existingAttendance,
        onStatusChange: () => {},
        statuses: attendanceStatuses,
        isReadOnly: true,
    };
    return (
        <PageContent
            breadcrumbItems={breadcrumbItems}
            pageClassName="-mt-16 md:-mt-20"
        >
            <BerandaAbsensiHeader {...absenHeaderData} />
            <div>
                <div className="px-1 py-4">
                    <h2 className="text-md md:text-lg text-neutral-800">
                        Daftar Kehadiran {tanggal} {namaBulan} {displayYear}
                    </h2>
                </div>
                <div className="hidden lg:block">
                    <BerandaAbsensiTable {...absenDataProps} />
                </div>
                <div className="lg:hidden">
                    <BerandaAbsensiCard {...absenDataProps} />
                </div>
            </div>
            <div className="mt-6 flex items-center justify-start">
                <ButtonRounded
                    as="link"
                    variant="outline"
                    href={route("beranda.absensi.day.show", {
                        kelas: selectedClass.kelas,
                        jurusan: selectedClass.jurusan,
                        tahun,
                        bulanSlug: bulanSlug,
                    })}
                >
                    <ArrowLeft size={16} className="mr-2" />
                    Kembali
                </ButtonRounded>
            </div>
        </PageContent>
    );
};
export default AbsensiPage;
