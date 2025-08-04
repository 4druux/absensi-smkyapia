import MainLayout from "@/Layouts/MainLayout";
import { Link } from "@inertiajs/react";
import { ArrowLeft } from "lucide-react";
import BreadcrumbNav from "../../Components/common/BreadcrumbNav";
import Button from "../../Components/common/Button";

const SelectDayPage = ({ tahun, bulan, namaBulan, days }) => {
    const breadcrumbItems = [
        { label: "Absensi", href: route("absensi.index") },
        { label: tahun, href: route("absensi.year.show", { tahun }) },
        {
            label: namaBulan,
            href: route("absensi.year.show", { tahun, bulan }),
        },
        { label: "Pilih Tanggal", href: null },
    ];

    return (
        <div>
            <BreadcrumbNav items={breadcrumbItems} />

            <div className="px-3 md:px-7 -mt-20 pb-10">
                <div className="bg-white shadow-lg rounded-2xl p-6 md:p-8">
                    <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7 gap-4">
                        {days.map((day) => (
                            <Link
                                key={day.nomor}
                                href={route("absensi.day.show", {
                                    tahun,
                                    bulan,
                                    tanggal: day.nomor,
                                })}
                                className="block group"
                            >
                                <div className="flex flex-col items-center justify-center p-4 bg-slate-50 hover:bg-sky-100 border border-slate-200 hover:border-sky-300 rounded-xl transition-all duration-200 cursor-pointer aspect-square">
                                    <p className="text-3xl font-bold text-sky-600 group-hover:scale-110 transition-transform">
                                        {day.nomor}
                                    </p>
                                    <p className="text-sm text-gray-500">
                                        {day.nama_hari}
                                    </p>
                                </div>
                            </Link>
                        ))}
                    </div>

                    <div className="flex justify-start mt-8">
                        <Button
                            as="link"
                            variant="outline"
                            href={route("absensi.year.show", { tahun })}
                        >
                            <ArrowLeft size={16} className="mr-2" />
                            Kembali
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    );
};

SelectDayPage.layout = (page) => (
    <MainLayout
        children={page}
        title={`Pilih Tanggal - ${page.props.namaBulan}`}
    />
);

export default SelectDayPage;
