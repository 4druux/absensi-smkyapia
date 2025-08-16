import useSWR from "swr";
import { fetcher } from "@/utils/api.js";

export const useBerandaDailyAttendance = (
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

    const { data: attendanceData, error, isLoading } = useSWR(swrKey, fetcher);

    const getAttendanceSummary = () => {
        if (
            !attendanceData ||
            !attendanceData.existingAttendance ||
            !attendanceData.students
        ) {
            return {
                hadir: 0,
                telat: 0,
                sakit: 0,
                izin: 0,
                alfa: 0,
                bolos: 0,
            };
        }

        const summary = {
            hadir: 0,
            telat: 0,
            sakit: 0,
            izin: 0,
            alfa: 0,
            bolos: 0,
        };

        Object.values(attendanceData.existingAttendance).forEach((status) => {
            if (status in summary) {
                summary[status]++;
            }
        });

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
        summary: getAttendanceSummary(),
        attendanceStatuses,
    };
};
