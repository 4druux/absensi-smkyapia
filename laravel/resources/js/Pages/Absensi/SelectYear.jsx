import React, { useState } from "react";
import { Link, router } from "@inertiajs/react";
import MainLayout from "@/Layouts/MainLayout";
import { CalendarDays, ArrowLeft, PlusCircle } from "lucide-react";
import toast from "react-hot-toast";
import BreadcrumbNav from "@/Components/common/BreadcrumbNav";
import Button from "@/Components/common/Button";

const SelectYear = ({ years, selectedClass }) => {
    const [processing, setProcessing] = useState(false);

    const breadcrumbItems = [
        { label: "Absensi", href: route("absensi.index") },
        {
            label: `${selectedClass.kelas} - ${selectedClass.jurusan}`,
            href: route("absensi.index"),
        },
        { label: "Pilih Tahun", href: null },
    ];

    const handleAddYear = (e) => {
        e.preventDefault();
        setProcessing(true);

        router.post(
            route("absensi.year.store"),
            {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
            },
            {
                onSuccess: () => {
                    toast.success("Tahun ajaran berhasil ditambahkan!");
                },
                onError: (errors) => {
                    console.error("Gagal menambahkan tahun:", errors);
                    toast.error("Gagal menambahkan tahun. Terjadi kesalahan.");
                },
                onFinish: () => {
                    setProcessing(false);
                },
            }
        );
    };

    return (
        <div>
            <BreadcrumbNav items={breadcrumbItems} />
            <div className="px-3 md:px-7 -mt-20 pb-10">
                <div className="bg-white shadow-lg rounded-2xl p-6 md:p-8">
                    <div className="flex justify-end items-center mb-6">
                        <Button
                            onClick={handleAddYear}
                            disabled={processing}
                            variant="primary"
                        >
                            <PlusCircle className="w-4 h-4 mr-2" />
                            <span>
                                {processing ? "Menambahkan..." : "Tambah Tahun"}
                            </span>
                        </Button>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        {years.map((year, index) => (
                            <Link
                                key={index}
                                href={route("absensi.year.show", {
                                    kelas: selectedClass.kelas,
                                    jurusan: selectedClass.jurusan,
                                    tahun: year.nomor,
                                })}
                                className="block group"
                            >
                                <div className="p-6 bg-slate-50 hover:bg-sky-100 border border-slate-200 hover:border-sky-300 rounded-xl transition-all duration-200 cursor-pointer text-center">
                                    <CalendarDays className="w-12 h-12 text-sky-500 mx-auto transition-transform duration-200 group-hover:scale-105" />
                                    <h4 className="mt-4 text-xl font-medium text-neutral-700">
                                        {year.nomor}
                                    </h4>
                                </div>
                            </Link>
                        ))}
                    </div>
                    <div className="flex justify-start mt-8">
                        <Button
                            as="link"
                            variant="outline"
                            href={route("absensi.index")}
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

SelectYear.layout = (page) => (
    <MainLayout children={page} title="Pilih Tahun" />
);

export default SelectYear;
