import { usePage } from "@inertiajs/react";
import PageContent from "@/Components/ui/page-content";
import CardContent from "@/Components/ui/card-content";
import ButtonRounded from "@/Components/common/button-rounded";
import { ClipboardCheck, Wallet, ArrowLeft } from "lucide-react";

const SelectTypePage = () => {
    const { selectedClass } = usePage().props;
    const { kelas, jurusan, kelompok } = selectedClass;

    const breadcrumbItems = [
        { label: "Beranda", href: route("home") },
        {
            label: `${kelas} ${kelompok} - ${jurusan}`,
            href: route("home"),
        },
        { label: "Pilih Laporan", href: null },
    ];

    return (
        <PageContent
            breadcrumbItems={breadcrumbItems}
            pageClassName="-mt-16 md:-mt-20"
        >
            <h3 className="text-md md:text-lg font-medium text-neutral-700 mb-4 md:mb-6">
                Pilih Laporan untuk Kelas {`${kelas} ${kelompok} `}
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <CardContent
                    href={route("beranda.absensi.year.show", {
                        kelas: kelas,
                        jurusan: jurusan,
                    })}
                    icon={ClipboardCheck}
                    title="Absensi"
                />
                <CardContent
                    href={route("beranda.uang-kas.year.show", {
                        kelas: kelas,
                        jurusan: jurusan,
                    })}
                    icon={Wallet}
                    title="Uang Kas"
                />
            </div>

            <div className="flex justify-start mt-8">
                <ButtonRounded as="link" variant="outline" href={route("home")}>
                    <ArrowLeft size={16} className="mr-2" />
                    Kembali
                </ButtonRounded>
            </div>
        </PageContent>
    );
};

export default SelectTypePage;
