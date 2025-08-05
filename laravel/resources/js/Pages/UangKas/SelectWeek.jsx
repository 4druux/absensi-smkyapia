import React from "react";
import MainLayout from "@/Layouts/MainLayout";
import { ArrowLeft, CalendarDays, CheckCircle } from "lucide-react";
import Button from "@/Components/common/Button";
import PageContent from "@/Components/common/PageContent";
import ContentCard from "@/Components/common/ContentCard";

const SelectWeek = ({
    minggu,
    tahun,
    bulanSlug,
    namaBulan,
    selectedClass,
    paidWeeks,
}) => {
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

    const isFullyPaid = (weekId) => {
        return paidWeeks.includes(weekId);
    };

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
                        href={route("uang-kas.week.show", {
                            kelas: selectedClass.kelas,
                            jurusan: selectedClass.jurusan,
                            tahun,
                            bulanSlug: bulanSlug,
                            minggu: week.id,
                        })}
                        variant={isFullyPaid(week.id) ? "success" : "default"}
                        title={week.label}
                        subtitle={`${week.start_date} s.d. ${week.end_date}`}
                    >
                        {isFullyPaid(week.id) && (
                            <div className="absolute -top-3 -right-3">
                                <CheckCircle className="w-5 h-5 text-green-600" />
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
                        tahun,
                    })}
                >
                    <ArrowLeft size={16} className="mr-2" />
                    Kembali
                </Button>
            </div>
        </PageContent>
    );
};

SelectWeek.layout = (page) => (
    <MainLayout
        children={page}
        title={`Pilih Minggu - ${page.props.namaBulan}`}
    />
);

export default SelectWeek;
