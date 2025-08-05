import { useState } from "react";
import MainLayout from "@/Layouts/MainLayout";
import { Link, router } from "@inertiajs/react";
import { BsPlusLg } from "react-icons/bs";
import { Calendar } from "lucide-react";
import toast from "react-hot-toast";
import BreadcrumbNav from "../../Components/common/BreadcrumbNav";

const SelectYearPage = ({ years }) => {
    const breadcrumbItems = [{ label: "Absensi", href: null }];

    const [processing, setProcessing] = useState(false);

    const handleAddYear = (e) => {
        e.preventDefault();
        setProcessing(true);

        router.post(
            route("absensi.year.store"),
            {},
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
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        {years.map((year) => (
                            <Link
                                key={year.nomor}
                                href={route("absensi.year.show", {
                                    tahun: year.nomor,
                                })}
                                className="block group"
                            >
                                <div className="p-6 bg-slate-50 hover:bg-sky-100 border border-slate-200 hover:border-sky-300 rounded-xl transition-all duration-200 cursor-pointer text-center h-full flex flex-col justify-center items-center">
                                    <Calendar className="w-12 h-12 text-sky-500 mx-auto transition-transform duration-200 group-hover:scale-105" />
                                    <h4 className="mt-4 text-xl font-medium text-neutral-700">
                                        {year.nomor}
                                    </h4>
                                </div>
                            </Link>
                        ))}

                        <Link
                            href={route("absensi.year.store")}
                            onClick={handleAddYear}
                            as="button"
                            type="button"
                            disabled={processing}
                            className="block group focus:outline-none"
                        >
                            <div className="flex flex-col justify-center items-center p-6 bg-sky-50 hover:bg-sky-100 border-2 border-dashed border-sky-300 hover:border-sky-400 rounded-xl transition-all duration-200 text-center h-full cursor-pointer">
                                <BsPlusLg className="w-12 h-12 text-sky-500 mx-auto transition-transform duration-200 group-hover:scale-105" />
                                <h4 className="mt-4 text-lg font-medium text-neutral-700">
                                    {processing
                                        ? "Menambahkan..."
                                        : "Tambah Tahun"}
                                </h4>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
};

SelectYearPage.layout = (page) => (
    <MainLayout children={page} title="Pilih Tahun" />
);

export default SelectYearPage;
