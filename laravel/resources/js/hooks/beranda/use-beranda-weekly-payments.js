import useSWR from "swr";
import { fetcher } from "@/utils/api.js";

export const useBerandaWeeklyPayments = (
    kelas,
    jurusan,
    tahun,
    bulanSlug,
    minggu
) => {
    const swrKey =
        kelas && jurusan && tahun && bulanSlug && minggu
            ? `/uang-kas/${kelas}/${jurusan}/payments/${tahun}/${bulanSlug}/${minggu}`
            : null;

    const { data: paymentsData, error, isLoading } = useSWR(swrKey, fetcher);

    const getSummary = () => {
        if (!paymentsData || !paymentsData.students) {
            return {
                totalStudents: 0,
                paidStudents: 0,
                unpaidStudents: 0,
                totalCollected: 0,
                target: 0,
            };
        }

        const paidStudentsCount = Object.values(
            paymentsData.existingPayments || {}
        ).filter((payment) => payment.status === "paid").length;

        const nominalFromDb =
            Object.values(paymentsData.existingPayments || {})[0]?.nominal || 0;

        const totalCollected =
            paidStudentsCount * (parseInt(nominalFromDb) || 0);

        return {
            totalStudents: paymentsData.students.length,
            paidStudents: paidStudentsCount,
            unpaidStudents: paymentsData.students.length - paidStudentsCount,
            totalCollected: totalCollected,
            target:
                paymentsData.students.length * (parseInt(nominalFromDb) || 0),
        };
    };

    return {
        payments: paymentsData?.existingPayments || {},
        fixedNominal:
            Object.values(paymentsData?.existingPayments || {})[0]?.nominal ||
            0,
        isLoading,
        error,
        dbSummary: getSummary(),
    };
};
