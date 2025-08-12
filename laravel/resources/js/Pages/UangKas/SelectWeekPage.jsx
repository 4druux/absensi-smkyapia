import { useEffect } from "react";
import { ArrowLeft, CheckCircle } from "lucide-react";
import toast from "react-hot-toast";
import { usePage } from "@inertiajs/react";

// Components
import MainLayout from "@/Layouts/MainLayout";
import ButtonRounded from "@/Components/common/button-rounded";
import PageContent from "@/Components/ui/page-content";
import CardContent from "@/Components/ui/card-content";
import DotLoader from "@/Components/ui/dot-loader";
import { useUangKasWeeks } from "@/hooks/uang-kas/use-uang-kas-weeks";

const SelectWeekPage = ({ tahun, bulanSlug, namaBulan, selectedClass }) => {
    const { flash } = usePage().props;

    const { weeks, isLoading, error } = useUangKasWeeks(
        selectedClass.kelas,
        selectedClass.jurusan,
        tahun,
        bulanSlug
    );

    useEffect(() => {
        if (flash && flash.success) {
            toast.success(flash.success);
        }
        if (flash && flash.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-screen">
                <DotLoader text="Memuat data mingguan..." />
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex items-center justify-center h-screen">
                Gagal memuat data minggu.
            </div>
        );
    }

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
                Pilih Minggu ({namaBulan} {displayYear})
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                {weeks &&
                    weeks.map((week) => (
                        <CardContent
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
                            subtitle={
                                week.display_date_range || week.display_date
                            }
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
                        </CardContent>
                    ))}
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
