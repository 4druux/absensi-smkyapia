import { useState, useEffect } from "react";
import useSWR from "swr";
import toast from "react-hot-toast";
import { fetcher } from "@/utils/api";
import { storeOtherPayments } from "@/services/uang-kas/uang-kas-service";

export const useOtherPayments = (kelas, jurusan, iuranId) => {
    const swrKey =
        kelas && jurusan && iuranId
            ? `/uang-kas/${kelas}/${jurusan}/other-cash/${iuranId}/payments`
            : null;

    const {
        data: paymentsData,
        error,
        isLoading,
        mutate,
    } = useSWR(swrKey, fetcher);

    const [payments, setPayments] = useState({});
    const [fixedNominal, setFixedNominal] = useState(0);
    const [isProcessing, setIsProcessing] = useState(false);

    const hasAnyPaymentBeenSaved = !!(
        paymentsData &&
        Object.keys(paymentsData.existingPayments).length > 0 &&
        Object.values(paymentsData.existingPayments).some((p) => p.nominal > 0)
    );

    const allStudentsPaidFromDb =
        hasAnyPaymentBeenSaved &&
        paymentsData.students?.every(
            (student) =>
                paymentsData.existingPayments[student.id]?.status === "paid"
        );

    useEffect(() => {
        if (paymentsData && paymentsData.students) {
            const initialState = {};
            paymentsData.students.forEach((student) => {
                initialState[student.id] = {
                    status:
                        paymentsData.existingPayments[student.id]?.status ||
                        "unpaid",
                };
            });
            setPayments(initialState);
            const firstPaidPayment = Object.values(
                paymentsData.existingPayments
            ).find((p) => p.status === "paid" && p.nominal > 0);
            if (firstPaidPayment) {
                setFixedNominal(firstPaidPayment.nominal);
            } else {
                setFixedNominal(0);
            }
        }
    }, [paymentsData]);

    const handlePaymentChange = (studentId, field, value) => {
        setPayments((prev) => ({
            ...prev,
            [studentId]: {
                ...prev[studentId],
                [field]: value,
            },
        }));
    };

    const handleNominalChange = (value) => {
        setFixedNominal(value);
    };

    const handleSelectAllChange = (checked) => {
        const newPayments = {};
        paymentsData.students.forEach((student) => {
            const existingStatus =
                paymentsData.existingPayments[student.id]?.status || "unpaid";
            if (existingStatus === "paid") {
                newPayments[student.id] = { status: "paid" };
            } else {
                newPayments[student.id] = {
                    status: checked ? "paid" : "unpaid",
                };
            }
        });
        setPayments(newPayments);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (parseInt(fixedNominal) <= 0) {
            toast.error("Nominal iuran harus lebih dari 0.");
            window.scrollTo({ top: 0, behavior: "smooth" });
            return;
        }

        const payload = {
            fixed_nominal: parseInt(fixedNominal),
            payments: Object.entries(payments).map(([siswa_id, data]) => ({
                siswa_id: parseInt(siswa_id),
                status: data.status,
            })),
        };

        setIsProcessing(true);
        try {
            const response = await storeOtherPayments(
                kelas,
                jurusan,
                iuranId,
                payload
            );
            toast.success(
                response.message || "Pembayaran iuran berhasil diperbarui!"
            );
            window.scrollTo({ top: 0, behavior: "smooth" });
            mutate();
        } catch (err) {
            toast.error(
                err.response?.data?.message || "Gagal menyimpan pembayaran."
            );
        } finally {
            setIsProcessing(false);
        }
    };

    const getDbSummary = () => {
        if (!paymentsData || !paymentsData.students) return {};
        const paidStudentsCount = Object.values(
            paymentsData.existingPayments
        ).filter((payment) => payment.status === "paid").length;
        const nominalFromDb =
            Object.values(paymentsData.existingPayments)[0]?.nominal || 0;
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

    const hasChanges = () => {
        if (!paymentsData || !paymentsData.students) return false;
        for (const student of paymentsData.students) {
            const currentStatus = payments[student.id]?.status || "unpaid";
            const existingStatus =
                paymentsData.existingPayments[student.id]?.status || "unpaid";
            if (currentStatus !== existingStatus) return true;
        }
        return false;
    };

    return {
        payments,
        fixedNominal,
        isProcessing,
        isLoading,
        error,
        handlePaymentChange,
        handleNominalChange,
        handleSelectAllChange,
        handleSubmit,
        dbSummary: getDbSummary(),
        hasChanges,
        hasAnyPaymentBeenSaved,
        allStudentsPaidFromDb,
    };
};
