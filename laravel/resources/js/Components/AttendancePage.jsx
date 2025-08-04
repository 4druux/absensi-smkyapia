import { useState, useEffect } from "react";
import { router, Link } from "@inertiajs/react";
import toast from "react-hot-toast";
import { Save, ArrowLeft } from "lucide-react";
import BreadcrumbNav from "./common/BreadcrumbNav";
import AbsensiTable from "./absensi/AbsensiTable";
import AbesensiCard from "./absensi/AbsensiCard";
import AbsensiHeader from "./absensi/AbsensiHeader";
import AbsensiNotFound from "./absensi/AbsensiNotFound";
import Button from "./common/Button";

const AttendancePage = ({
    studentData,
    tanggal,
    bulan,
    namaBulan,
    tahun,
    initialAttendance = {},
    tanggalAbsen,
}) => {
    if (
        !studentData ||
        !studentData.students ||
        studentData.students.length === 0
    ) {
        const breadcrumbItems = [
            { label: "Absensi", href: route("absensi.index") },
            { label: tahun, href: route("absensi.year.show", { tahun }) },
            {
                label: namaBulan,
                href: route("absensi.month.show", { tahun, bulan }),
            },
            { label: "Data Tidak Ditemukan", href: null },
        ];

        return (
            <AbsensiNotFound
                breadcrumbItems={breadcrumbItems}
                title="Data Siswa Kosong"
                message="Tidak ditemukan data siswa untuk tanggal ini. Silakan input data siswa terlebih dahulu pada tab 'Input Data Siswa'."
            />
        );
    }

    const [attendance, setAttendance] = useState({});
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        const initialState = {};
        if (studentData?.students) {
            studentData.students.forEach((student) => {
                initialState[student.id] =
                    initialAttendance[student.id] || null;
            });
        }
        setAttendance(initialState);
    }, [studentData, initialAttendance]);

    const handleAttendanceChange = (studentId, status) => {
        setAttendance((prev) => {
            const newAttendance = { ...prev };
            newAttendance[studentId] =
                newAttendance[studentId] === status ? null : status;
            return newAttendance;
        });
    };

    const handleSubmit = (e) => {
        e.preventDefault();
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
            route("absensi.day.store", { tahun, bulan, tanggal }),
            payload,
            {
                preserveState: true,
                onStart: () => setProcessing(true),
                onFinish: () => setProcessing(false),
                onSuccess: () => {
                    toast.success("Absensi berhasil disimpan!");
                },
                onError: (errors) => {
                    console.error(errors);
                    toast.error("Gagal menyimpan. Periksa error di console.");
                },
            }
        );
    };

    const attendanceStatuses = [
        {
            key: "telat",
            label: "Telat",
            color: "bg-yellow-100 text-yellow-800 border-yellow-300",
        },
        {
            key: "sakit",
            label: "Sakit",
            color: "bg-blue-100 text-blue-800 border-blue-300",
        },
        {
            key: "izin",
            label: "Izin",
            color: "bg-green-100 text-green-800 border-green-300",
        },
        {
            key: "alfa",
            label: "Alfa",
            color: "bg-red-100 text-red-800 border-red-300",
        },
        {
            key: "bolos",
            label: "Bolos",
            color: "bg-purple-100 text-purple-800 border-purple-300",
        },
    ];

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
        { label: tahun, href: route("absensi.year.show", { tahun }) },
        {
            label: namaBulan,
            href: route("absensi.month.show", { tahun, bulan }),
        },
        {
            label: "Presensi Harian",
            href: null,
        },
    ];

    return (
        <form onSubmit={handleSubmit}>
            <div>
                <BreadcrumbNav items={breadcrumbItems} />

                <div className="px-3 md:px-7 -mt-20 pb-10">
                    <div className="bg-white shadow-lg rounded-2xl p-4 md:p-8 flex flex-col space-y-6">
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

                            <div className="hidden md:block">
                                <AbsensiTable
                                    students={studentData.students}
                                    attendance={attendance}
                                    onStatusChange={handleAttendanceChange}
                                    statuses={attendanceStatuses}
                                />
                            </div>

                            <div className="md:hidden">
                                <AbesensiCard
                                    students={studentData.students}
                                    attendance={attendance}
                                    onStatusChange={handleAttendanceChange}
                                    statuses={attendanceStatuses}
                                />
                            </div>
                        </div>

                        <div className="mt-6 flex items-center justify-end space-x-4">
                            <Button
                                as="link"
                                variant="outline"
                                href={route("absensi.month.show", {
                                    tahun,
                                    bulan,
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
                    </div>
                </div>
            </div>
        </form>
    );
};

export default AttendancePage;
