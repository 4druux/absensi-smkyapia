import { useState, useEffect } from "react";
import { router } from "@inertiajs/react";
import toast from "react-hot-toast";
import { Save, ArrowLeft } from "lucide-react";
import MainLayout from "@/Layouts/MainLayout";
import PageContent from "@/Components/common/PageContent";
import Button from "@/Components/common/Button";
import DataNotFound from "@/Components/common/DataNotFound";
import UangKasHeader from "@/Components/uangkas/UangKasHeader";
import UangKasTable from "@/Components/uangkas/UangKasTable";
import UangKasCard from "@/Components/uangkas/UangKasCard";

const UangKasPage = ({
    studentData,
    tahun,
    bulanSlug,
    minggu,
    existingPayments,
    selectedClass,
    namaBulan,
}) => {
    if (
        !studentData ||
        !studentData.students ||
        studentData.students.length === 0
    ) {
        const notFoundBreadcrumb = [
            { label: "Uang Kas", href: route("uang-kas.index") },
            {
                label: `${selectedClass.kelas} - ${selectedClass.jurusan}`,
                href: route("absensi.index"),
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
                minggu: minggu,
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

    const [payments, setPayments] = useState({});
    const [fixedNominal, setFixedNominal] = useState(0);
    const [processing, setProcessing] = useState(false);

    const hasAnyPaymentBeenSaved = Object.keys(existingPayments).length > 0;
    const isReadOnly = hasAnyPaymentBeenSaved;
    const allStudentsPaidFromDb = studentData.students.every(
        (student) => existingPayments[student.id]?.status === "paid"
    );

    useEffect(() => {
        const initialState = {};
        if (studentData?.students) {
            studentData.students.forEach((student) => {
                initialState[student.id] = {
                    status: existingPayments[student.id]?.status || "unpaid",
                };
            });
        }
        setPayments(initialState);

        const firstPaidPayment = Object.values(existingPayments).find(
            (p) => p.status === "paid"
        );
        if (firstPaidPayment) {
            setFixedNominal(firstPaidPayment.nominal);
        }
    }, [studentData, existingPayments]);

    const handlePaymentChange = (studentId, field, value) => {
        if (existingPayments[studentId]?.status === "paid") {
            return;
        }

        setPayments((prev) => ({
            ...prev,
            [studentId]: {
                ...prev[studentId],
                [field]: value,
            },
        }));
    };

    const handleSelectAllChange = (checked) => {
        const newPayments = {};
        studentData.students.forEach((student) => {
            const existingStatus =
                existingPayments[student.id]?.status || "unpaid";
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

    const handleNominalChange = (value) => {
        if (isReadOnly) {
            return;
        }
        setFixedNominal(value);
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        if (parseInt(fixedNominal) <= 0) {
            toast.error("Nominal uang kas harus lebih dari 0.");
            return;
        }

        const payload = {
            fixed_nominal: parseInt(fixedNominal),
            payments: Object.entries(payments).map(([siswa_id, data]) => ({
                siswa_id: parseInt(siswa_id),
                status: data.status,
            })),
        };

        router.post(
            route("uang-kas.week.store", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun,
                bulanSlug,
                minggu,
            }),
            payload,
            {
                preserveState: true,
                onStart: () => setProcessing(true),
                onFinish: () => setProcessing(false),
                onSuccess: () => {
                    toast.success("Pembayaran uang kas berhasil diperbarui!");
                },
                onError: (errors) => {
                    if (errors.fixed_nominal) {
                        toast.error(errors.fixed_nominal);
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

    const getDbSummary = () => {
        const paidStudentsCount = Object.values(existingPayments).filter(
            (payment) => payment.status === "paid"
        ).length;

        const nominalFromDb = Object.values(existingPayments)[0]?.nominal || 0;

        const totalCollected =
            paidStudentsCount * (parseInt(nominalFromDb) || 0);

        return {
            totalStudents: studentData.students.length,
            paidStudents: paidStudentsCount,
            unpaidStudents: studentData.students.length - paidStudentsCount,
            totalCollected: totalCollected,
            target:
                studentData.students.length * (parseInt(nominalFromDb) || 0),
        };
    };

    const hasChanges = () => {
        for (const student of studentData.students) {
            const currentStatus = payments[student.id]?.status || "unpaid";
            const existingStatus =
                existingPayments[student.id]?.status || "unpaid";
            if (currentStatus !== existingStatus) {
                return true;
            }
        }
        return false;
    };

    const dbSummary = getDbSummary();

    const breadcrumbItems = [
        { label: "Uang Kas", href: route("uang-kas.index") },
        {
            label: `${selectedClass.kelas} - ${selectedClass.jurusan}`,
            href: route("absensi.index"),
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
            minggu: minggu,
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
                    isReadOnly={isReadOnly}
                />

                <div>
                    <div className="px-1 py-4">
                        <h2 className="text-lg text-neutral-800">
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
                        disabled={processing || !hasChanges()}
                    >
                        <Save className="w-4 h-4 mr-2" />
                        {processing
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
