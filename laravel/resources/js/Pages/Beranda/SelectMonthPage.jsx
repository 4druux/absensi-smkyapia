import { useEffect, useRef, useState } from "react";
import toast from "react-hot-toast";
import { usePage } from "@inertiajs/react";
import { ArrowLeft, CalendarDays } from "lucide-react";

// Components
import CardContent from "@/Components/ui/card-content";
import DotLoader from "@/Components/ui/dot-loader";
import PageContent from "@/Components/ui/page-content";
import DataNotFound from "@/Components/ui/data-not-found";
import ButtonRounded from "@/Components/common/button-rounded";
import { useBerandaMonths } from "@/hooks/beranda/use-beranda-months";

const SelectMonthPage = ({ selectedClass, tahun, type }) => {
    const { flash } = usePage().props;

    const { months, error, isLoading } = useBerandaMonths(
        selectedClass.kelas,
        selectedClass.jurusan,
        tahun,
        type
    );

    useEffect(() => {
        if (flash && flash.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    const breadcrumbItems = [
        { label: "Beranda", href: route("home") },
        {
            label: `${selectedClass.kelas} ${selectedClass.kelompok} - ${selectedClass.jurusan}`,
            href: route("home"),
        },
        {
            label: type === "absensi" ? "Absensi" : "Uang Kas",
            href: route("beranda.class.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
            }),
        },
        {
            label: tahun,
            href: route(
                `beranda.${
                    type === "absensi" ? "absensi" : "uang-kas"
                }.year.show`,
                {
                    kelas: selectedClass.kelas,
                    jurusan: selectedClass.jurusan,
                }
            ),
        },
        { label: "Pilih Bulan", href: null },
    ];

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-screen">
                <DotLoader text="Memuat daftar bulan..." />
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex items-center justify-center h-screen">
                Gagal memuat data bulan.
            </div>
        );
    }
    return (
        <PageContent
            breadcrumbItems={breadcrumbItems}
            pageClassName="-mt-16 md:-mt-20"
        >
            <h3 className="text-md md:text-lg font-medium text-neutral-700 mb-4 md:mb-6">
                Pilih Bulan Tahun Ajaran {tahun}
            </h3>
            {months && months.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    {months.map((month) => (
                        <CardContent
                            key={month.slug}
                            href={route(
                                type === "absensi"
                                    ? `beranda.absensi.day.show`
                                    : `beranda.uang-kas.week.show`,
                                {
                                    kelas: selectedClass.kelas,
                                    jurusan: selectedClass.jurusan,
                                    tahun: tahun,
                                    bulanSlug: month.slug,
                                }
                            )}
                            icon={CalendarDays}
                            title={month.nama}
                            subtitle={
                                month.nama === "Juli" ||
                                month.nama === "Agustus" ||
                                month.nama === "September" ||
                                month.nama === "Oktober" ||
                                month.nama === "November" ||
                                month.nama === "Desember"
                                    ? tahun.split("-")[0]
                                    : tahun.split("-")[1]
                            }
                        />
                    ))}
                </div>
            ) : (
                <DataNotFound
                    title="Data Bulan Kosong"
                    message="Belum ada data bulan yang tersedia."
                />
            )}

            <div className="flex justify-start mt-8">
                <ButtonRounded
                    as="link"
                    variant="outline"
                    href={route(
                        `beranda.${
                            type === "absensi" ? "absensi" : "uang-kas"
                        }.year.show`,
                        {
                            kelas: selectedClass.kelas,
                            jurusan: selectedClass.jurusan,
                        }
                    )}
                >
                    <ArrowLeft size={16} className="mr-2" />
                    Kembali
                </ButtonRounded>
            </div>
        </PageContent>
    );
};

export default SelectMonthPage;
