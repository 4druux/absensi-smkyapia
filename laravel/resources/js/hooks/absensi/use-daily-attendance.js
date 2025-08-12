import { useState, useEffect } from "react";
import useSWR from "swr";
import toast from "react-hot-toast";
import { router } from "@inertiajs/react";
import { fetcher } from "@/utils/api";
import { storeDailyAttendance } from "@/services/absensi/absensi-service";

export const useDailyAttendance = (
    kelas,
    jurusan,
    tahun,
    bulanSlug,
    tanggal
) => {
    const swrKey =
        kelas && jurusan && tahun && bulanSlug && tanggal
            ? `/absensi/${kelas}/${jurusan}/attendance/${tahun}/${bulanSlug}/${tanggal}`
            : null;

    const {
        data: attendanceData,
        error,
        isLoading,
        mutate,
    } = useSWR(swrKey, fetcher);
    const [isProcessing, setIsProcessing] = useState(false);
    const [attendance, setAttendance] = useState({});

    useEffect(() => {
        if (attendanceData && attendanceData.students) {
            const initialState = {};
            attendanceData.students.forEach((student) => {
                initialState[student.id] =
                    attendanceData.existingAttendance[student.id] || null;
            });
            setAttendance(initialState);
        }
    }, [attendanceData]);

    const hasAttendanceBeenSaved = !!(
        attendanceData && attendanceData.tanggalAbsen
    );

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

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (hasAttendanceBeenSaved) {
            toast.error(
                "Absensi untuk hari ini sudah dicatat dan tidak bisa diubah."
            );
            return;
        }

        if (
            !attendanceData ||
            !attendanceData.students ||
            attendanceData.students.length === 0
        ) {
            toast.error("Tidak ada siswa untuk diabsen.");
            return;
        }

        const allStudentIdsOnPage = attendanceData.students.map(
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

        setIsProcessing(true);
        try {
            await storeDailyAttendance(
                kelas,
                jurusan,
                tahun,
                bulanSlug,
                tanggal,
                payload
            );
            toast.success("Absensi berhasil disimpan!");
            mutate();
            router.visit(
                route("absensi.day.show", {
                    kelas,
                    jurusan,
                    tahun,
                    bulanSlug,
                    tanggal,
                })
            );
        } catch (err) {
            toast.error(
                err.response?.data?.message || "Gagal menyimpan absensi."
            );
        } finally {
            setIsProcessing(false);
        }
    };

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
        const totalStudents = attendanceData?.students?.length || 0;

        Object.values(attendance).forEach((status) => {
            if (status === null) summary.notMarked++;
            else if (Object.prototype.hasOwnProperty.call(summary, status))
                summary[status]++;
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

    const attendanceStatuses = [
        "hadir",
        "telat",
        "sakit",
        "izin",
        "alfa",
        "bolos",
    ].map((status) => ({
        key: status,
        label: status.charAt(0).toUpperCase() + status.slice(1),
        color:
            status === "hadir"
                ? "bg-green-100 text-green-800 border-green-300"
                : "bg-red-100 text-red-800 border-red-300",
    }));

    return {
        attendanceData,
        isLoading,
        error,
        isProcessing,
        hasAttendanceBeenSaved,
        attendance,
        attendanceStatuses,
        summary: getAttendanceSummary(),
        handleAttendanceChange,
        handleSubmit,
        mutate,
    };
};
