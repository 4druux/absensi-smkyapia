import { useState } from "react";
import {
    ArrowLeft,
    ArrowUpDown,
    CheckCircle,
    MinusCircle,
    PlusCircle,
} from "lucide-react";

import ButtonRounded from "@/Components/common/button-rounded";
import PageContent from "@/Components/ui/page-content";
import CardContent from "@/Components/ui/card-content";
import DotLoader from "@/Components/ui/dot-loader";
import { useUangKasWeeks } from "@/hooks/uang-kas/use-uang-kas-weeks";
import { useUangKasOther } from "@/hooks/uang-kas/use-uang-kas-other";
import UangKasOtherModal from "@/Components/uang-kas/uang-kas-other-modal";
import { FaMoneyBillWave } from "react-icons/fa6";
import { useUangKasSummary } from "@/hooks/uang-kas/use-uang-kas-summary";
import { formatRupiah } from "@/utils/formatRupiah";

const SelectWeekPage = ({ tahun, bulanSlug, namaBulan, selectedClass }) => {
    const [isOpenModal, setIsOpenModal] = useState(false);

    const {
        weeks: weeklyData,
        isLoading: isLoadingWeeks,
        error: errorWeeks,
    } = useUangKasWeeks(
        selectedClass.kelas,
        selectedClass.jurusan,
        tahun,
        bulanSlug
    );

    const {
        otherCashData,
        isLoading: isLoadingOther,
        error: errorOther,
        mutate: mutateOtherCash,
    } = useUangKasOther(
        selectedClass.kelas,
        selectedClass.jurusan,
        tahun,
        bulanSlug
    );

    // Panggil hook baru untuk summary
    const { summary, isLoading: isLoadingSummary } = useUangKasSummary(
        selectedClass.kelas,
        selectedClass.jurusan,
        tahun,
        bulanSlug
    );

    const isLoading = isLoadingWeeks || isLoadingOther || isLoadingSummary;
    const error = errorWeeks || errorOther;

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-screen">
                <DotLoader text="Memuat data..." />
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex items-center justify-center h-screen">
                Gagal memuat data.
            </div>
        );
    }

    let displayYear;
    const [startYear, endYear] = tahun.split("-");
    const month = namaBulan.toLowerCase();

    if (
        ["januari", "februari", "maret", "april", "mei", "juni"].includes(month)
    ) {
        displayYear = endYear;
    } else {
        displayYear = startYear;
    }

    const breadcrumbItems = [
        { label: "Uang Kas", href: route("uang-kas.index") },
        {
            label: `${selectedClass.kelas} ${selectedClass.kelompok} - ${selectedClass.jurusan}`,
            href: route("uang-kas.index"),
        },
        {
            label: tahun,
            href: route("uang-kas.class.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
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
        { label: "Pilih Iuran", href: null },
    ];

    return (
        <PageContent
            breadcrumbItems={breadcrumbItems}
            pageClassName="-mt-16 md:-mt-20"
        >
            <div className="flex flex-col md:flex-row md:justify-between md:items-center mb-4 md:mb-6 gap-6">
                <div className="flex items-center space-x-2 md:space-x-3">
                    <div className="p-3 rounded-lg bg-sky-100">
                        <FaMoneyBillWave className="w-5 h-5 md:w-6 md:h-6 text-sky-600" />
                    </div>
                    <div>
                        <h3 className="text-md md:text-lg font-medium text-neutral-700">
                            Pilih Iuran ({namaBulan} {displayYear})
                        </h3>
                        <div className="flex flex-row gap-4 md:items-center">
                            <div
                                className={`flex items-center space-x-1 ${
                                    summary?.total_pemasukan > 0
                                        ? "text-green-600"
                                        : "text-neutral-500"
                                }`}
                            >
                                <ArrowUpDown className="w-5 h-5" />
                                <div className="flex flex-col text-xs md:text-sm">
                                    Pemasukan
                                    <span className="font-medium text-center">
                                        {summary?.total_pemasukan
                                            ? formatRupiah(
                                                  summary.total_pemasukan
                                              )
                                            : "-"}
                                    </span>
                                </div>
                            </div>
                            <div
                                className={`flex items-center space-x-1 ${
                                    summary?.total_pengeluaran > 0
                                        ? "text-red-500"
                                        : "text-neutral-500"
                                }`}
                            >
                                <ArrowUpDown className="w-5 h-5" />
                                <div className="flex flex-col text-xs md:text-sm">
                                    Pengeluaran
                                    <span className="font-medium text-center">
                                        {summary?.total_pengeluaran
                                            ? formatRupiah(
                                                  summary.total_pengeluaran
                                              )
                                            : "-"}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="flex flex-col md:flex-row items-end md:items-center justify-end gap-2">
                    <ButtonRounded
                        as="link"
                        variant="outline"
                        size="sm"
                        href={route("uang-kas.pengeluaran.index", {
                            kelas: selectedClass.kelas,
                            jurusan: selectedClass.jurusan,
                            tahun: tahun,
                            bulanSlug: bulanSlug,
                        })}
                    >
                        <MinusCircle className="w-4 h-4 mr-1 md:mr-2" />
                        <span className="text-xs md:text-sm font-medium">
                            Pengeluaran kas
                        </span>
                    </ButtonRounded>
                    <ButtonRounded
                        disabled={isLoading}
                        variant="primary"
                        size="sm"
                        onClick={() => setIsOpenModal(true)}
                    >
                        <PlusCircle className="w-4 h-4 mr-1 md:mr-2" />
                        <span className="text-xs md:text-sm font-medium">
                            Tambah Iuran Lain
                        </span>
                    </ButtonRounded>
                </div>
            </div>

            <div>
                <h4 className="text-base font-medium text-neutral-600 mb-4">
                    Iuran Mingguan
                </h4>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    {weeklyData &&
                        weeklyData.map((card) => (
                            <CardContent
                                key={`weekly-${card.id}`}
                                href={
                                    card.is_holiday
                                        ? null
                                        : route("uang-kas.week.show", {
                                              kelas: selectedClass.kelas,
                                              jurusan: selectedClass.jurusan,
                                              tahun,
                                              bulanSlug,
                                              minggu: card.id,
                                          })
                                }
                                variant={
                                    card.is_paid
                                        ? "success"
                                        : card.is_holiday
                                        ? "error"
                                        : "default"
                                }
                                title={card.label}
                                subtitle={
                                    card.display_date_range || card.display_date
                                }
                            >
                                {card.is_paid && (
                                    <div className="absolute -top-4 -right-3">
                                        <CheckCircle className="w-5 h-5 text-green-600" />
                                    </div>
                                )}
                                {card.is_holiday && (
                                    <div className="absolute -top-4 -right-4 text-sm font-semibold text-red-600">
                                        LIBUR
                                    </div>
                                )}
                            </CardContent>
                        ))}
                </div>
            </div>

            <div className="mt-8">
                <h4 className="text-base font-medium text-neutral-600 mb-4">
                    Iuran Lainnya
                </h4>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    {otherCashData && otherCashData.length > 0 ? (
                        otherCashData.map((card) => (
                            <CardContent
                                key={card.id}
                                href={route("uang-kas.other-cash.show", {
                                    kelas: selectedClass.kelas,
                                    jurusan: selectedClass.jurusan,
                                    tahun: tahun,
                                    bulanSlug: bulanSlug,
                                    iuran: card.other_id,
                                })}
                                variant={card.is_paid ? "success" : "default"}
                                title={card.label}
                                subtitle={card.display_date}
                            >
                                {card.is_paid && (
                                    <div className="absolute -top-4 -right-3">
                                        <CheckCircle className="w-5 h-5 text-green-600" />
                                    </div>
                                )}
                            </CardContent>
                        ))
                    ) : (
                        <div className="col-span-4 text-center py-24 text-neutral-500 text-sm">
                            Belum ada iuran lainnya di bulan ini.
                        </div>
                    )}
                </div>
            </div>

            <div className="flex justify-start mt-8">
                <ButtonRounded
                    as="link"
                    variant="outline"
                    href={route("uang-kas.year.show", {
                        kelas: selectedClass.kelas,
                        jurusan: selectedClass.jurusan,
                        tahun: tahun,
                    })}
                >
                    <ArrowLeft size={16} className="mr-2" />
                    Kembali
                </ButtonRounded>
            </div>

            <UangKasOtherModal
                isOpen={isOpenModal}
                onClose={() => setIsOpenModal(false)}
                selectedClass={selectedClass}
                onSuccess={() => mutateOtherCash()}
                displayYear={displayYear}
                bulanSlug={bulanSlug}
                namaBulan={namaBulan}
            />
        </PageContent>
    );
};

export default SelectWeekPage;
