// resources/js/Pages/Absensi/SelectDay.jsx
import MainLayout from "@/Layouts/MainLayout";
import { Link } from "@inertiajs/react";
import { ArrowLeft, CheckCircle } from "lucide-react";
import BreadcrumbNav from "@/Components/common/BreadcrumbNav";
import Button from "@/Components/common/Button";

const SelectDayPage = ({
    tahun,
    bulan,
    namaBulan,
    days,
    absensiDays,
    selectedClass,
}) => {
    const breadcrumbItems = [
        { label: "Absensi", href: route("absensi.index") },
        {
            label: `${selectedClass.kelas} - ${selectedClass.jurusan}`,
            href: route("absensi.index"),
        },
        {
            label: tahun,
            href: route("absensi.class.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
            }),
        },
        {
            label: `${namaBulan}`,
            href: route("absensi.year.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun: tahun,
            }),
        },
        { label: "Pilih Tanggal", href: null },
    ];

    const hasAbsensi = (day) => {
        return absensiDays.includes(day);
    };

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
                                    kelas: selectedClass.kelas,
                                    jurusan: selectedClass.jurusan,
                                    tahun,
                                    bulanSlug: bulan,
                                    tanggal: day.nomor,
                                })}
                                className="block group"
                            >
                                <div
                                    className={`relative flex flex-col items-center justify-center p-4 rounded-xl transition-all duration-200 cursor-pointer aspect-square ${
                                        hasAbsensi(day.nomor)
                                            ? "bg-green-100 border-green-300"
                                            : "bg-slate-50 hover:bg-sky-100 border-slate-200 hover:border-sky-300"
                                    }`}
                                >
                                    {hasAbsensi(day.nomor) && (
                                        <div className="absolute top-2 right-2">
                                            <CheckCircle className="w-5 h-5 text-green-600" />
                                        </div>
                                    )}
                                    <p
                                        className={`text-3xl font-bold transition-transform ${
                                            hasAbsensi(day.nomor)
                                                ? "text-green-600"
                                                : "text-sky-600"
                                        } group-hover:scale-110`}
                                    >
                                        {day.nomor}
                                    </p>
                                    <p
                                        className={`text-sm ${
                                            hasAbsensi(day.nomor)
                                                ? "text-green-600"
                                                : "text-gray-500"
                                        }`}
                                    >
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
                            href={route("absensi.year.show", {
                                kelas: selectedClass.kelas,
                                jurusan: selectedClass.jurusan,
                                tahun,
                            })}
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
