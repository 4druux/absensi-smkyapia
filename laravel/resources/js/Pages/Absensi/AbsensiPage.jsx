import { useState, useEffect } from "react";
import { router } from "@inertiajs/react";
import toast from "react-hot-toast";
import { Save, ArrowLeft } from "lucide-react";
import MainLayout from "@/Layouts/MainLayout";
import PageContent from "@/Components/common/PageContent";
import AbsensiTable from "@/Components/absensi/AbsensiTable";
import AbesensiCard from "@/Components/absensi/AbsensiCard";
import AbsensiHeader from "@/Components/absensi/AbsensiHeader";
import Button from "@/Components/common/Button";
import DataNotFound from "../../Components/common/DataNotFound";

const AbsensiPage = ({
    studentData,
    tanggal,
    bulan,
    namaBulan,
    tahun,
    existingAttendance,
    tanggalAbsen,
    selectedClass,
}) => {
    if (
        !studentData ||
        !studentData.students ||
        studentData.students.length === 0
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

    const [attendance, setAttendance] = useState({});
    const [processing, setProcessing] = useState(false);
    const hasAttendanceBeenSaved = !!tanggalAbsen;

    useEffect(() => {
        const initialState = {};
        if (studentData?.students) {
            studentData.students.forEach((student) => {
                initialState[student.id] =
                    existingAttendance[student.id] || null;
            });
        }
        setAttendance(initialState);
    }, [studentData, existingAttendance]);

    const handleAttendanceChange = (studentId, status) => {
        if (hasAttendanceBeenSaved) {
            return;
        }

        setAttendance((prev) => {
            const newAttendance = { ...prev };
            newAttendance[studentId] =
                newAttendance[studentId] === status ? null : status;
            return newAttendance;
        });
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        if (hasAttendanceBeenSaved) {
            toast.error(
                "Absensi untuk hari ini sudah dicatat dan tidak bisa diubah."
            );
            return;
        }
        const allStudentIdsOnPage = studentData.students.map(
            (student) => student.id
        );
        const payload = {
            attendance: Object.entries(attendance)
                .filter(([, status]) => status !== null)
                .map(([siswa_id, status]) => ({
                    siswa_id,
                    status,
                })),
            all_student_ids: allStudentIdsOnPage,
        };
        router.post(
            route("absensi.day.store", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun,
                bulanSlug: bulan,
                tanggal,
            }),
            payload,
            {
                preserveState: true,
                onStart: () => setProcessing(true),
                onFinish: () => setProcessing(false),
                onSuccess: () => {
                    toast.success("Absensi berhasil disimpan!");
                },
                onError: (errors) => {
                    if (errors.absensi) {
                        toast.error(errors.absensi);
                    } else {
                        console.error(errors);
                        toast.error(
                            "Gagal menyimpan. Periksa error di console."
                        );
                    }
                },
            }
        );
    };

    const attendanceStatuses = [
        "hadir",
        "telat",
        "sakit",
        "izin",
        "alfa",
        "bolos",
    ].map((status) => {
        const isPresent = status === "hadir";
        return {
            key: status,
            label: status.charAt(0).toUpperCase() + status.slice(1),
            color: isPresent
                ? "bg-green-100 text-green-800 border-green-300"
                : "bg-red-100 text-red-800 border-red-300",
        };
    });

    const getAttendanceSummary = () => {
        const summary = {
            present: 0,
            telat: 0,
            sakit: 0,
            izin: 0,
            alfa: 0,
            bolos: 0,
            notMarked: 0,
        };
        const totalStudents = studentData.students.length;

        Object.values(attendance).forEach((status) => {
            if (status === null) summary.notMarked++;
            else if (summary.hasOwnProperty(status)) summary[status]++;
        });

        summary.present =
            totalStudents -
            (summary.telat +
                summary.sakit +
                summary.izin +
                summary.alfa +
                summary.bolos +
                summary.notMarked);
        return summary;
    };

    const summary = getAttendanceSummary();

    const breadcrumbItems = [
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
        { label: "Presensi Harian", href: null },
    ];

    return (
        <form onSubmit={handleSubmit}>
            <PageContent
                breadcrumbItems={breadcrumbItems}
                pageClassName="-mt-16 md:-mt-20"
            >
                <AbsensiHeader
                    studentData={studentData}
                    tanggalAbsen={tanggalAbsen}
                    summary={summary}
                />

                <div>
                    <div className="px-1 py-4">
                        <h2 className="text-lg text-neutral-800">
                            Daftar Kehadiran
                        </h2>
                    </div>

                    <div className="hidden lg:block">
                        <AbsensiTable
                            students={studentData.students}
                            attendance={attendance}
                            onStatusChange={handleAttendanceChange}
                            statuses={attendanceStatuses}
                            isReadOnly={hasAttendanceBeenSaved}
                        />
                    </div>

                    <div className="lg:hidden">
                        <AbesensiCard
                            students={studentData.students}
                            attendance={attendance}
                            onStatusChange={handleAttendanceChange}
                            statuses={attendanceStatuses}
                            isReadOnly={hasAttendanceBeenSaved}
                        />
                    </div>
                </div>

                <div className="mt-6 flex items-center justify-end space-x-4">
                    <Button
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
                    </Button>

                    <Button
                        type="submit"
                        variant="primary"
                        disabled={processing}
                    >
                        <Save className="w-4 h-4 mr-2" />
                        {processing ? "Menyimpan..." : "Simpan"}
                    </Button>
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
