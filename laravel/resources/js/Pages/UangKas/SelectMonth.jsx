import MainLayout from "@/Layouts/MainLayout";
import { ArrowLeft, CalendarDays } from "lucide-react";
import PageContent from "@/Components/common/PageContent";
import ContentCard from "@/Components/common/ContentCard";
import Button from "@/Components/common/Button";

const SelectMonth = ({ months, tahun, selectedClass }) => {
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
        { label: "Pilih Bulan", href: null },
    ];

    return (
        <PageContent
            breadcrumbItems={breadcrumbItems}
            pageClassName="-mt-16 md:-mt-20"
        >
            <h3 className="text-md md:text-lg font-medium text-neutral-700 mb-4 md:mb-6">
                Pilih Bulan ({tahun})
            </h3>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                {months.map((month) => (
                    <ContentCard
                        key={month.id}
                        href={route("uang-kas.month.show", {
                            kelas: selectedClass.kelas,
                            jurusan: selectedClass.jurusan,
                            tahun,
                            bulanSlug: month.slug,
                        })}
                        icon={CalendarDays}
                        title={month.label}
                    />
                ))}
            </div>

            <div className="flex justify-start mt-8">
                <Button
                    as="link"
                    variant="outline"
                    href={route("uang-kas.class.show", {
                        kelas: selectedClass.kelas,
                        jurusan: selectedClass.jurusan,
                    })}
                >
                    <ArrowLeft size={16} className="mr-2" />
                    Kembali
                </Button>
            </div>
        </PageContent>
    );
};

SelectMonth.layout = (page) => (
    <MainLayout children={page} title={`Pilih Bulan - ${page.props.tahun}`} />
);

export default SelectMonth;
