// resources/js/Pages/UangKas/SelectWeek.jsx

import { useEffect } from "react";
import MainLayout from "@/Layouts/MainLayout";
import { ArrowLeft, CheckCircle } from "lucide-react";
import Button from "@/Components/common/Button";
import PageContent from "@/Components/common/PageContent";
import ContentCard from "@/Components/common/ContentCard";
import { usePage } from "@inertiajs/react";
import toast from "react-hot-toast";

const SelectWeekPage = ({
    tahun,
    bulanSlug,
    namaBulan,
    minggu,
    selectedClass,
    paidWeeks,
    holidays,
}) => {
    const { flash } = usePage().props;

    useEffect(() => {
        if (flash && flash.success) {
            toast.success(flash.success);
        }
        if (flash && flash.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    const isPaid = (weekId) => {
        return paidWeeks.includes(weekId);
    };

    const isHoliday = (week) => {
        const weekDates = week.days.map((day) => {
            const date = `${tahun}-${bulanSlug}-${day.nomor}`;
            return date;
        });

        return weekDates.every((date) => holidays.includes(date));
    };

    const breadcrumbItems = [
        { label: "Uang Kas", href: route("uang-kas.index") },
        {
            label: `${selectedClass.kelas} - ${selectedClass.jurusan}`,
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
        { label: "Pilih Minggu", href: null },
    ];

    return (
        <PageContent
            breadcrumbItems={breadcrumbItems}
            pageClassName="-mt-16 md:-mt-20"
        >
            <h3 className="text-md md:text-lg font-medium text-neutral-700 mb-4 md:mb-6">
                Pilih Minggu ({namaBulan} {tahun})
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                {minggu.map((week) => (
                    <ContentCard
                        key={week.id}
                        href={
                            week.is_paid || week.is_holiday
                                ? null
                                : route("uang-kas.week.show", {
                                      kelas: selectedClass.kelas,
                                      jurusan: selectedClass.jurusan,
                                      tahun,
                                      bulanSlug: bulanSlug,
                                      minggu: week.id,
                                  })
                        }
                        variant={
                            week.is_paid
                                ? "success"
                                : week.is_holiday
                                ? "error"
                                : "default"
                        }
                        title={week.label}
                        // PERUBAHAN: Menggunakan logika baru untuk subtitle
                        subtitle={week.display_date_range || week.display_date}
                    >
                        {week.is_paid && (
                            <div className="absolute -top-4 -right-3">
                                <CheckCircle className="w-5 h-5 text-green-600" />
                            </div>
                        )}
                        {week.is_holiday && (
                            <div className="absolute -top-4 -right-4 text-sm font-semibold text-red-600">
                                LIBUR
                            </div>
                        )}
                    </ContentCard>
                ))}
            </div>

            <div className="flex justify-start mt-8">
                <Button
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
                </Button>
            </div>
        </PageContent>
    );
};

SelectWeekPage.layout = (page) => (
    <MainLayout
        children={page}
        title={`Pilih Minggu - ${page.props.namaBulan}`}
    />
);

export default SelectWeekPage;
