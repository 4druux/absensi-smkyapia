import { FaMoneyBillWave } from "react-icons/fa6";
import { ArrowLeft, ArrowUpDown, CheckCircle } from "lucide-react";
import ButtonRounded from "@/Components/common/button-rounded";
import PageContent from "@/Components/ui/page-content";
import CardContent from "@/Components/ui/card-content";
import DotLoader from "@/Components/ui/dot-loader";
import DataNotFound from "@/Components/ui/data-not-found";
import { useBerandaWeeks } from "@/hooks/beranda/use-beranda-weeks";
import { useBerandaOtherCash } from "@/hooks/beranda/use-beranda-other-cash";
import { useBerandaSummary } from "@/hooks/beranda/use-beranda-summary";
import { formatRupiah } from "@/utils/formatRupiah";
import { usePage } from "@inertiajs/react";

const SelectUangKasWeekPage = ({
    tahun,
    bulanSlug,
    namaBulan,
    selectedClass,
}) => {
    const { flash } = usePage().props;

    const {
        weeks: weeklyData,
        isLoading: isLoadingWeeks,
        error: errorWeeks,
    } = useBerandaWeeks(
        selectedClass.kelas,
        selectedClass.jurusan,
        tahun,
        bulanSlug
    );

    const {
        otherCashData,
        isLoading: isLoadingOther,
        error: errorOther,
    } = useBerandaOtherCash(
        selectedClass.kelas,
        selectedClass.jurusan,
        tahun,
        bulanSlug
    );

    const { summary, isLoading: isLoadingSummary } = useBerandaSummary(
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
        { label: "Beranda", href: route("home") },
        {
            label: `${selectedClass.kelas} ${selectedClass.kelompok} - ${selectedClass.jurusan}`,
            href: route("home"),
        },
        {
            label: "Uang Kas",
            href: route("beranda.class.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
            }),
        },
        {
            label: tahun,
            href: route("beranda.uang-kas.year.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
            }),
        },
        {
            label: namaBulan,
            href: route("beranda.uang-kas.month.show", {
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
            </div>

            <div>
                <h4 className="text-base font-medium text-neutral-600 mb-4">
                    Iuran Mingguan
                </h4>
                {weeklyData && weeklyData.length > 0 ? (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        {weeklyData.map((card) => (
                            <CardContent
                                key={`weekly-${card.id}`}
                                href={route("beranda.uang-kas.weekly.show", {
                                    kelas: selectedClass.kelas,
                                    jurusan: selectedClass.jurusan,
                                    tahun: tahun,
                                    bulanSlug: bulanSlug,
                                    minggu: card.minggu_ke,
                                })}
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
                ) : (
                    <DataNotFound
                        title="Data Iuran Mingguan Kosong"
                        message="Belum ada data iuran mingguan di bulan ini."
                    />
                )}
            </div>

            <div className="mt-8">
                <h4 className="text-base font-medium text-neutral-600 mb-4">
                    Iuran Lainnya
                </h4>
                {otherCashData && otherCashData.length > 0 ? (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        {otherCashData.map((card) => (
                            <CardContent
                                key={card.id}
                                href={route("beranda.uang-kas.other.show", {
                                    kelas: selectedClass.kelas,
                                    jurusan: selectedClass.jurusan,
                                    iuranId: card.id,
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
                        ))}
                    </div>
                ) : (
                    <div className="col-span-4 text-center py-24 text-neutral-500 text-sm">
                        Belum ada iuran lainnya di bulan ini.
                    </div>
                )}
            </div>

            <div className="flex justify-start mt-8">
                <ButtonRounded
                    as="link"
                    variant="outline"
                    href={route("beranda.uang-kas.month.show", {
                        kelas: selectedClass.kelas,
                        jurusan: selectedClass.jurusan,
                        tahun: tahun,
                    })}
                >
                    <ArrowLeft size={16} className="mr-2" />
                    Kembali
                </ButtonRounded>
            </div>
        </PageContent>
    );
};

export default SelectUangKasWeekPage;
