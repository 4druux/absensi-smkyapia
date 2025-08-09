import { useEffect, useRef, useState } from "react";

import toast from "react-hot-toast";
import { usePage } from "@inertiajs/react";
import { AnimatePresence, motion } from "framer-motion";
import { ArrowLeft, CalendarDays, Loader2 } from "lucide-react";
import { FaFilePdf } from "react-icons/fa6";
import { IoIosMore } from "react-icons/io";
import { RiFileExcel2Line } from "react-icons/ri";

// Components
import MainLayout from "@/Layouts/MainLayout";
import Button from "@/Components/common/button";
import CardContent from "@/Components/ui/card-content";
import DotLoader from "@/Components/ui/dot-loader";
import PageContent from "@/Components/ui/page-content";
import { useAbsensiMonths } from "@/hooks/absensi/use-absensi-months";
import { dropdownAnimation } from "@/hooks/use-dropdown";

const SelectMonthPage = ({ tahun, selectedClass }) => {
    const { flash } = usePage().props;

    const { months, error, isLoading, handleExport, downloadingStatus } =
        useAbsensiMonths(selectedClass.kelas, selectedClass.jurusan, tahun);

    const [isDropdownOpen, setIsDropdownOpen] = useState(null);
    const dropdownRef = useRef(null);

    useEffect(() => {
        if (flash && flash.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (
                isDropdownOpen &&
                dropdownRef.current &&
                !dropdownRef.current.contains(event.target)
            ) {
                const isDownloading = Object.values(downloadingStatus).some(
                    (status) => status === true
                );

                if (!isDownloading) {
                    setIsDropdownOpen(null);
                }
            }
        };

        if (isDropdownOpen) {
            document.addEventListener("mousedown", handleClickOutside);
        }

        return () => {
            document.removeEventListener("mousedown", handleClickOutside);
        };
    }, [isDropdownOpen, downloadingStatus]);

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

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-screen">
                <DotLoader text="Memuat daftar bulan..." />
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex items-center justify-center h-screen">
                Gagal memuat data bulan.
            </div>
        );
    }

    return (
        <PageContent
            breadcrumbItems={breadcrumbItems}
            pageClassName="-mt-16 md:-mt-20"
        >
            <h3 className="text-md md:text-lg font-medium text-neutral-700 mb-4 md:mb-6">
                Pilih Bulan Tahun Ajaran {tahun}
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                {months &&
                    months.map((month) => (
                        <CardContent
                            key={month.slug}
                            href={route("absensi.month.show", {
                                kelas: selectedClass.kelas,
                                jurusan: selectedClass.jurusan,
                                tahun: tahun,
                                bulanSlug: month.slug,
                            })}
                            icon={CalendarDays}
                            title={month.nama}
                            subtitle={
                                month.nama === "Juli" ||
                                month.nama === "Agustus" ||
                                month.nama === "September" ||
                                month.nama === "Oktober" ||
                                month.nama === "November" ||
                                month.nama === "Desember"
                                    ? tahun.split("-")[0]
                                    : tahun.split("-")[1]
                            }
                        >
                            <div
                                className="absolute -top-6 -right-6 z-20"
                                ref={
                                    isDropdownOpen === month.slug
                                        ? dropdownRef
                                        : null
                                }
                            >
                                <Button
                                    size="sm"
                                    variant="icon"
                                    onClick={(e) => {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        setIsDropdownOpen(
                                            isDropdownOpen === month.slug
                                                ? false
                                                : month.slug
                                        );
                                    }}
                                >
                                    <IoIosMore size={16} />
                                </Button>
                                <AnimatePresence>
                                    {isDropdownOpen === month.slug && (
                                        <motion.div
                                            initial="hidden"
                                            animate="visible"
                                            exit="hidden"
                                            variants={
                                                dropdownAnimation.variants
                                            }
                                            transition={
                                                dropdownAnimation.transition
                                            }
                                            className="absolute right-0 top-8 w-48 rounded-lg shadow-lg bg-white border border-slate-200"
                                        >
                                            <div className="px-1 py-3 space-y-1">
                                                <div
                                                    className="block w-full text-left p-3 text-sm rounded-md cursor-pointer text-neutral-700 hover:bg-sky-50 hover:text-sky-600"
                                                    onClick={(e) => {
                                                        e.preventDefault();
                                                        e.stopPropagation();
                                                        handleExport(
                                                            month.slug,
                                                            "excel"
                                                        );
                                                    }}
                                                >
                                                    {downloadingStatus[
                                                        `${month.slug}-excel`
                                                    ] ? (
                                                        <span className="flex items-center gap-1">
                                                            <Loader2 className="w-5 h-5 animate-spin" />
                                                            Mengekspor...
                                                        </span>
                                                    ) : (
                                                        <span className="flex items-center gap-1">
                                                            <RiFileExcel2Line className="w-5 h-5" />
                                                            Eksport Excel
                                                        </span>
                                                    )}
                                                </div>
                                                <div
                                                    className="block w-full text-left p-3 text-sm rounded-md cursor-pointer text-neutral-700 hover:bg-sky-50 hover:text-sky-600"
                                                    onClick={(e) => {
                                                        e.preventDefault();
                                                        e.stopPropagation();
                                                        handleExport(
                                                            month.slug,
                                                            "pdf"
                                                        );
                                                    }}
                                                >
                                                    {downloadingStatus[
                                                        `${month.slug}-pdf`
                                                    ] ? (
                                                        <span className="flex items-center gap-1">
                                                            <Loader2 className="w-5 h-5 animate-spin" />
                                                            Mengekspor...
                                                        </span>
                                                    ) : (
                                                        <span className="flex items-center gap-1">
                                                            <FaFilePdf className="w-5 h-5" />
                                                            Export PDF
                                                        </span>
                                                    )}
                                                </div>
                                            </div>
                                        </motion.div>
                                    )}
                                </AnimatePresence>
                            </div>
                        </CardContent>
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
        </PageContent>
    );
};

SelectMonthPage.layout = (page) => (
    <MainLayout children={page} title={`Pilih Bulan - ${page.props.tahun}`} />
);

export default SelectMonthPage;
