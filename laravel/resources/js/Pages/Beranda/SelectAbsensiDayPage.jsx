import { useEffect, useState, useRef } from "react";
import toast from "react-hot-toast";
import { usePage } from "@inertiajs/react";
import { ArrowLeft, CheckCircle } from "lucide-react";

// Components
import PageContent from "@/Components/ui/page-content";
import CardContent from "@/Components/ui/card-content";
import DotLoader from "@/Components/ui/dot-loader";
import DataNotFound from "@/Components/ui/data-not-found";
import { useBerandaAbsensiDays } from "@/hooks/beranda/use-beranda-absensi-days";
import ButtonRounded from "@/Components/common/button-rounded";

const SelectAbsensiDayPage = ({
    selectedClass,
    tahun,
    bulanSlug,
    namaBulan,
}) => {
    const { flash } = usePage().props;

    const { days, absensiDays, holidays, isLoading, error } =
        useBerandaAbsensiDays(
            selectedClass.kelas,
            selectedClass.jurusan,
            tahun,
            bulanSlug
        );

    useEffect(() => {
        if (flash && flash.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    let displayYear;
    const [startYear, endYear] = tahun.split("-");
    const month = namaBulan.toLowerCase();

    if (
        month === "januari" ||
        month === "februari" ||
        month === "maret" ||
        month === "april" ||
        month === "mei" ||
        month === "juni"
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
            label: "Absensi",
            href: route("beranda.class.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
            }),
        },
        {
            label: tahun,
            href: route("beranda.absensi.year.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
            }),
        },
        {
            label: namaBulan,
            href: route("beranda.absensi.month.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun: tahun,
                bulan: bulanSlug,
            }),
        },
        { label: "Pilih tanggal", href: null },
    ];

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-screen">
                <DotLoader text="Memuat data kalender..." />
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex items-center justify-center h-screen">
                Gagal memuat data kalender.
            </div>
        );
    }

    const hasAbsensi = (day) => {
        return Array.isArray(absensiDays) && absensiDays.includes(day);
    };

    const isHoliday = (day) => {
        return Array.isArray(holidays) && holidays.includes(day.nomor);
    };

    return (
        <PageContent
            breadcrumbItems={breadcrumbItems}
            pageClassName="-mt-16 md:-mt-20"
        >
            <h3 className="text-md md:text-lg font-medium text-neutral-700 mb-4 md:mb-6">
                Pilih Hari ({namaBulan} {displayYear})
            </h3>

            {days && days.length > 0 ? (
                <>
                    <div className="hidden md:grid md:grid-cols-7 gap-4">
                        {[
                            "Minggu",
                            "Senin",
                            "Selasa",
                            "Rabu",
                            "Kamis",
                            "Jumat",
                            "Sabtu",
                        ].map((dayName) => (
                            <div
                                key={dayName}
                                className="text-center font-semibold text-sm text-neutral-500"
                            >
                                {dayName}
                            </div>
                        ))}
                    </div>

                    <div className="grid grid-cols-2 md:grid-cols-7 gap-4 mt-2">
                        {days.map((day, index) => {
                            if (day.is_outside_month) {
                                return (
                                    <div
                                        key={`outside-${day.nomor}-${index}`}
                                        className="flex flex-col items-center justify-center rounded-lg h-24 relative bg-slate-50 text-neutral-400 cursor-not-allowed"
                                    >
                                        <span className="text-xl font-semibold">
                                            {day.nomor}
                                        </span>
                                        <span className="text-xs text-neutral-400">
                                            {day.nama_hari}
                                        </span>
                                    </div>
                                );
                            }

                            const isCurrentDayHoliday = isHoliday(day);
                            const hasAbsensiData = hasAbsensi(day.nomor);
                            const cardVariant = hasAbsensiData
                                ? "success"
                                : isCurrentDayHoliday
                                ? "error"
                                : "default";

                            return (
                                <CardContent
                                    key={`inside-${day.nomor}`}
                                    href={
                                        isCurrentDayHoliday
                                            ? null
                                            : route(
                                                  "beranda.absensi.day.detail",
                                                  {
                                                      kelas: selectedClass.kelas,
                                                      jurusan:
                                                          selectedClass.jurusan,
                                                      tahun,
                                                      bulanSlug,
                                                      tanggal: day.nomor,
                                                  }
                                              )
                                    }
                                    variant={cardVariant}
                                    title={day.nomor}
                                    subtitle={day.nama_hari}
                                >
                                    {isCurrentDayHoliday && (
                                        <div className="absolute -top-4 -right-4 text-xs font-semibold text-red-600">
                                            LIBUR
                                        </div>
                                    )}
                                    {hasAbsensiData && (
                                        <div className="absolute -top-4 -right-3">
                                            <CheckCircle className="w-5 h-5 text-green-600" />
                                        </div>
                                    )}
                                </CardContent>
                            );
                        })}
                    </div>
                </>
            ) : (
                <DataNotFound
                    title="Data Hari Kosong"
                    message="Belum ada data hari yang tersedia di bulan ini."
                />
            )}

            <div className="flex justify-start mt-8">
                <ButtonRounded
                    as="link"
                    variant="outline"
                    href={route("beranda.absensi.month.show", {
                        kelas: selectedClass.kelas,
                        jurusan: selectedClass.jurusan,
                        tahun,
                    })}
                >
                    <ArrowLeft size={16} className="mr-2" />
                    Kembali
                </ButtonRounded>
            </div>
        </PageContent>
    );
};

export default SelectAbsensiDayPage;
