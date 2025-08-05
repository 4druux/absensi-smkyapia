// resources/js/Pages/Absensi/SelectMonth.jsx

import React from "react";
import { Link } from "@inertiajs/react";
import MainLayout from "@/Layouts/MainLayout";
import { Calendar, ArrowLeft } from "lucide-react";
import BreadcrumbNav from "@/Components/common/BreadcrumbNav";
import Button from "@/Components/common/Button";

const SelectMonth = ({ months, tahun, selectedClass }) => {
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
        { label: "Pilih Bulan", href: null },
    ];

    return (
        <div>
            <BreadcrumbNav items={breadcrumbItems} />
            <div className="px-3 md:px-7 -mt-20 pb-10">
                <div className="bg-white shadow-lg rounded-2xl p-6 md:p-8">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        {months.map((month, index) => (
                            <Link
                                key={index}
                                href={route("absensi.month.show", {
                                    kelas: selectedClass.kelas,
                                    jurusan: selectedClass.jurusan,
                                    tahun: tahun,
                                    bulanSlug: month.slug,
                                })}
                                className="block group"
                            >
                                <div className="p-6 bg-slate-50 hover:bg-sky-100 border border-slate-200 hover:border-sky-300 rounded-xl transition-all duration-200 cursor-pointer text-center">
                                    <Calendar className="w-12 h-12 text-sky-500 mx-auto transition-transform duration-200 group-hover:scale-105" />
                                    <h4 className="mt-4 text-lg font-medium text-neutral-700">
                                        {month.nama}
                                    </h4>
                                </div>
                            </Link>
                        ))}
                    </div>
                    <div className="flex justify-start mt-8">
                        <Button
                            as="link"
                            variant="outline"
                            href={route("absensi.class.show", {
                                kelas: selectedClass.kelas,
                                jurusan: selectedClass.jurusan,
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

SelectMonth.layout = (page) => (
    <MainLayout children={page} title={`Pilih Bulan - ${page.props.tahun}`} />
);

export default SelectMonth;
