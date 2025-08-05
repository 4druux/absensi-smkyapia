import React from "react";
import { Link, router } from "@inertiajs/react";
import MainLayout from "@/Layouts/MainLayout";
import { School, PlusCircle, Trash2 } from "lucide-react";
import BreadcrumbNav from "@/Components/common/BreadcrumbNav";
import toast from "react-hot-toast";

const AllClasses = ({ classes }) => {
    const breadcrumbItems = [
        { label: "Data Siswa", href: route("data-siswa.index") },
        { label: "Daftar Kelas", href: null },
    ];

    const handleDeleteClass = (kelas, jurusan) => {
        if (
            confirm(
                `Apakah Anda yakin ingin menghapus kelas ${kelas} - ${jurusan} beserta semua data siswanya?`
            )
        ) {
            router.delete(
                route("data-siswa.class.destroy", { kelas, jurusan }),
                {
                    onSuccess: () => {
                        toast.success(
                            `Kelas ${kelas} - ${jurusan} berhasil dihapus.`
                        );
                    },
                    onError: (errors) => {
                        toast.error("Gagal menghapus kelas.");
                        console.error("Errors:", errors);
                    },
                }
            );
        }
    };

    return (
        <div>
            <BreadcrumbNav items={breadcrumbItems} />
            <div className="px-3 md:px-7 -mt-20 pb-10">
                <div className="bg-white shadow-lg rounded-2xl p-6 md:p-8">
                    <div className="flex justify-between items-center mb-6">
                        <h3 className="text-xl font-medium text-neutral-700">
                            Daftar Kelas
                        </h3>
                        <Link
                            href={route("data-siswa.input")}
                            className="inline-flex items-center space-x-2 text-sm font-medium text-white bg-sky-600 hover:bg-sky-700 px-4 py-2 rounded-full transition-colors"
                        >
                            <PlusCircle size={16} />
                            <span>Tambah Data Baru</span>
                        </Link>
                    </div>
                    {classes.length > 0 ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            {classes.map((c, index) => (
                                <div key={index} className="relative group">
                                    <Link
                                        href={route("data-siswa.class.show", {
                                            kelas: c.kelas,
                                            jurusan: c.jurusan,
                                        })}
                                        className="block"
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
                                    <button
                                        onClick={() =>
                                            handleDeleteClass(
                                                c.kelas,
                                                c.jurusan
                                            )
                                        }
                                        className="absolute top-2 right-2 p-2 rounded-full bg-red-500 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                                        aria-label={`Hapus kelas ${c.kelas}-${c.jurusan}`}
                                    >
                                        <Trash2 size={16} />
                                    </button>
                                </div>
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

AllClasses.layout = (page) => (
    <MainLayout children={page} title="Daftar Kelas" />
);

export default AllClasses;
