import { useState } from "react";
import { ArrowLeft, PlusCircle } from "lucide-react";
import { usePage } from "@inertiajs/react";
import { FaMoneyBillWave } from "react-icons/fa6";
import { BookOpen, School, ArrowUpDown } from "lucide-react";

import PageContent from "@/Components/ui/page-content";
import ButtonRounded from "@/Components/common/button-rounded";
import DotLoader from "@/Components/ui/dot-loader";
import PengeluaranModal from "@/Components/uang-kas/pengeluaran-modal";
import PengeluaranTable from "@/Components/uang-kas/pengeluaran-table";
import PengeluaranCard from "@/Components/uang-kas/pengeluaran-card";
import { usePengeluarans } from "@/hooks/uang-kas/use-pengeluarans";
import { formatRupiah } from "@/utils/formatRupiah";

const PengeluaranPage = ({ selectedClass, tahun, bulanSlug }) => {
    const { props: pageProps } = usePage();
    const [isModalOpen, setIsModalOpen] = useState(false);

    const { pengeluarans, totalPengeluaran, isLoading, error, mutate } =
        usePengeluarans(
            selectedClass.kelas,
            selectedClass.jurusan,
            tahun,
            bulanSlug
        );

    const currentUserRole =
        pageProps.auth?.user?.role === "admin" ? "wali_kelas" : "bendahara";

    const handleApprove = (id) => {
        alert(`API untuk menyetujui ID: ${id} akan dibuat.`);
    };

    const handleReject = (id) => {
        alert(`API untuk menolak ID: ${id} akan dibuat.`);
    };

    const monthMap = {
        januari: "Januari",
        februari: "Februari",
        maret: "Maret",
        april: "April",
        mei: "Mei",
        juni: "Juni",
        juli: "Juli",
        agustus: "Agustus",
        september: "September",
        oktober: "Oktober",
        november: "November",
        desember: "Desember",
    };
    const namaBulan = monthMap[bulanSlug] || "";

    const [startYear, endYear] = tahun.split("-");
    const displayYear = [
        "januari",
        "februari",
        "maret",
        "april",
        "mei",
        "juni",
    ].includes(bulanSlug)
        ? parseInt(endYear)
        : parseInt(startYear);

    const breadcrumbItems = [
        { label: "Uang Kas", href: route("uang-kas.index") },
        {
            label: `${selectedClass.kelas} ${selectedClass.kelompok} - ${selectedClass.jurusan}`,
            href: route("uang-kas.class.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
            }),
        },
        {
            label: tahun,
            href: route("uang-kas.year.show", {
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
            }),
        },
        {
            label: "Pengeluaran Kas",
            href: route("uang-kas.month.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun,
                bulanSlug,
            }),
        },
        { label: "Detail Pengeluaran", href: null },
    ];

    if (isLoading)
        return (
            <div className="flex items-center justify-center h-screen">
                <DotLoader text="Memuat data pengeluaran..." />
            </div>
        );

    if (!isLoading && error)
        return (
            <div className="flex items-center justify-center h-screen">
                Gagal memuat data
            </div>
        );

    return (
        <PageContent
            breadcrumbItems={breadcrumbItems}
            pageClassName="-mt-16 md:-mt-20"
        >
            <div className="flex flex-col md:flex-row md:justify-between md:items- mb-6">
                <div className="flex items-center space-x-2 md:space-x-3">
                    <div className="p-3 rounded-lg bg-sky-100">
                        <FaMoneyBillWave className="w-5 h-5 md:w-6 md:h-6 text-sky-600" />
                    </div>
                    <div>
                        <h3 className="text-md md:text-lg font-medium text-neutral-700">
                             Pengeluaran ({namaBulan} {displayYear})
                        </h3>
                        <div className="flex flex-row gap-2 md:mt-1 md:items-center">
                            <div className="flex items-center space-x-1 md:space-x-2 text-neutral-500">
                                <School className="hidden w-5 h-5 md:block" />
                                <span className="text-xs md:text-sm">
                                    {selectedClass.kelas}{" "}
                                    {selectedClass.kelompok}
                                </span>
                                <span className="block md:hidden">|</span>
                            </div>
                            <div className="flex items-center space-x-1 md:space-x-2 text-neutral-500">
                                <BookOpen className="hidden w-5 h-5 md:block" />
                                <span className="text-xs md:text-sm">
                                    {selectedClass.jurusan}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="flex items-center justify-end space-x-1 text-red-500">
                    <ArrowUpDown className="w-5 h-5" />
                    <div className="flex flex-col text-xs md:text-sm">
                        Total Disetujui
                        <span className="font-medium">
                            {formatRupiah(totalPengeluaran) || "-"}
                        </span>
                    </div>
                </div>
            </div>

            <div className="flex items-center justify-end mb-6">
                <ButtonRounded
                    variant="primary"
                    size="sm"
                    onClick={() => setIsModalOpen(true)}
                >
                    <PlusCircle className="w-4 h-4 mr-1 md:mr-2" />
                    <span className="text-xs md:text-sm font-medium">
                        Tambah Pengeluaran
                    </span>
                </ButtonRounded>
            </div>

            {!isLoading && !error && pengeluarans?.length > 0 ? (
                <>
                    <div className="hidden lg:block">
                        <PengeluaranTable
                            pengeluarans={pengeluarans}
                            role={currentUserRole}
                            onApprove={handleApprove}
                            onReject={handleReject}
                        />
                    </div>
                    <div className="lg:hidden">
                        <PengeluaranCard
                            pengeluarans={pengeluarans}
                            role={currentUserRole}
                            onApprove={handleApprove}
                            onReject={handleReject}
                        />
                    </div>
                </>
            ) : (
                !isLoading &&
                !error && (
                    <div className="text-center py-20 text-neutral-500 text-sm">
                        Belum ada data pengajuan pengeluaran.
                    </div>
                )
            )}

            <div className="flex justify-start mt-8">
                <ButtonRounded
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
                </ButtonRounded>
            </div>

            <PengeluaranModal
                isOpen={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                selectedClass={selectedClass}
                onSuccess={() => mutate()}
                displayYear={displayYear}
                namaBulan={namaBulan}
                bulanSlug={bulanSlug}
            />
        </PageContent>
    );
};

export default PengeluaranPage;
