import { usePage } from "@inertiajs/react";
import PageContent from "@/Components/ui/page-content";
import CardContent from "@/Components/ui/card-content";
import { ArrowLeft, CalendarRange } from "lucide-react";
import DataNotFound from "@/Components/ui/data-not-found";
import ButtonRounded from "@/Components/common/button-rounded";

const SelectYearPage = () => {
    const { selectedClass, years, type } = usePage().props;
    const { kelas, jurusan, kelompok } = selectedClass;
    const pageTitle = type === "absensi" ? "Absensi" : "Uang Kas";

    const breadcrumbItems = [
        { label: "Beranda", href: route("home") },
        {
            label: `${kelas} ${kelompok} - ${jurusan}`,
            href: route("home"),
        },
        {
            label: pageTitle,
            href: route("beranda.class.show", { kelas, jurusan }),
        },
        { label: "Pilih Tahun Ajaran", href: null },
    ];

    return (
        <PageContent breadcrumbItems={breadcrumbItems} pageClassName="-mt-16 md:-mt-20">
                <h3 className="text-md md:text-lg font-medium text-neutral-700 mb-4 md:mb-6">
                Pilih Tahun Ajaran
            </h3>
            {years && years.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    {years.map((year, index) => (
                        <CardContent
                            key={index}
                            href={route(`beranda.${type}.month.show`, {
                                kelas,
                                jurusan,
                                tahun: year.nomor,
                            })}
                            icon={CalendarRange}
                            title={year.nomor}
                        />
                    ))}
                </div>
            ) : (
                <DataNotFound
                    title="Data Tahun Ajaran Kosong"
                    message="Belum ada data tahun ajaran. Silakan tambahkan data tahun ajaran terlebih dahulu."
                />
            )}

            <div className="flex justify-start mt-8">
                <ButtonRounded
                    as="link"
                    variant="outline"
                    href={route("beranda.class.show", {
                        kelas,
                        jurusan,
                    })}
                >
                    <ArrowLeft size={16} className="mr-2" />
                    Kembali
                </ButtonRounded>
            </div>
        </PageContent>
    );
};

export default SelectYearPage;
