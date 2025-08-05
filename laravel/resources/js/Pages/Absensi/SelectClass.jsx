// resources/js/Pages/Absensi/SelectClass.jsx
import React from "react";
import { Link } from "@inertiajs/react";
import MainLayout from "@/Layouts/MainLayout";
import { School } from "lucide-react";
import BreadcrumbNav from "@/Components/common/BreadcrumbNav";

const SelectClass = ({ classes }) => {
    const breadcrumbItems = [
        { label: "Absensi", href: route("absensi.index") },
        { label: "Pilih Kelas", href: null },
    ];

    return (
        <div>
            <BreadcrumbNav items={breadcrumbItems} />
            <div className="px-3 md:px-7 -mt-20 pb-10">
                <div className="bg-white shadow-lg rounded-2xl p-6 md:p-8">
                    {classes.length > 0 ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            {classes.map((c, index) => (
                                <Link
                                    key={index}
                                    href={route("absensi.class.show", {
                                        kelas: c.kelas,
                                        jurusan: c.jurusan,
                                    })}
                                    className="block group"
                                >
                                    <div className="p-6 bg-slate-50 hover:bg-sky-100 border border-slate-200 hover:border-sky-300 rounded-xl transition-all duration-200 cursor-pointer text-center">
                                        <School className="w-12 h-12 text-sky-500 mx-auto transition-transform duration-200 group-hover:scale-105" />
                                        <h4 className="mt-4 text-xl font-medium text-neutral-700">
                                            {c.kelas}
                                        </h4>
                                        <p className="text-sm text-gray-500">
                                            {c.jurusan}
                                        </p>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    ) : (
                        <div className="text-center py-10 text-neutral-500">
                            Belum ada data kelas. Silakan tambahkan data siswa
                            terlebih dahulu.
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

SelectClass.layout = (page) => (
    <MainLayout children={page} title="Pilih Kelas Absensi" />
);

export default SelectClass;
