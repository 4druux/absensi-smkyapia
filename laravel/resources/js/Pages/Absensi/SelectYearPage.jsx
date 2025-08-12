import { useEffect, useRef, useState } from "react";
import { usePage } from "@inertiajs/react";
import { AnimatePresence, motion } from "framer-motion";
import { ArrowLeft, CalendarDays, PlusCircle, Loader2 } from "lucide-react";
import { IoIosMore } from "react-icons/io";
import { FaFilePdf } from "react-icons/fa6";
import { RiFileExcel2Line } from "react-icons/ri";
import toast from "react-hot-toast";

// Components
import MainLayout from "@/Layouts/MainLayout";
import ButtonRounded from "@/Components/common/button-rounded";
import CardContent from "@/Components/ui/card-content";
import DataNotFound from "@/Components/ui/data-not-found";
import DotLoader from "@/Components/ui/dot-loader";
import PageContent from "@/Components/ui/page-content";
import { useAbsensiYears } from "@/hooks/absensi/use-absensi-years";
import { dropdownAnimation } from "@/hooks/use-dropdown";

const SelectYearPage = ({ selectedClass }) => {
    const { flash } = usePage().props;
    const {
        years,
        isLoading,
        error,
        handleAddYear,
        handleExportYear,
        downloadingStatus,
    } = useAbsensiYears();

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

    const handleAddYearClick = (e) => {
        e.preventDefault();
        handleAddYear();
    };

    const breadcrumbItems = [
        { label: "Absensi", href: route("absensi.index") },
        {
            label: `${selectedClass.kelas} ${selectedClass.kelompok} - ${selectedClass.jurusan}`,
            href: route("absensi.index"),
        },
        { label: "Pilih Tahun Ajaran", href: null },
    ];

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-screen">
                <DotLoader text="Memuat daftar tahun..." />
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex items-center justify-center h-screen">
                Gagal memuat data tahun.
            </div>
        );
    }

    return (
        <PageContent
            breadcrumbItems={breadcrumbItems}
            pageClassName="-mt-16 md:-mt-20"
        >
            <div className="flex justify-between items-center mb-6">
                <h3 className="text-md md:text-lg font-medium text-neutral-700">
                    Pilih Tahun Ajaran
                </h3>
                <ButtonRounded
                    onClick={handleAddYearClick}
                    disabled={isLoading}
                    variant="primary"
                    size="sm"
                >
                    <PlusCircle className="w-4 h-4 mr-1 md:mr-2" />
                    <span className="text-xs md:text-sm font-medium">
                        {isLoading ? "Memuat..." : "Tambah Tahun"}
                    </span>
                </ButtonRounded>
            </div>
            {years && years.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    {years.map((year, index) => (
                        <CardContent
                            key={index}
                            href={route("absensi.year.show", {
                                kelas: selectedClass.kelas,
                                jurusan: selectedClass.jurusan,
                                tahun: year.nomor,
                            })}
                            icon={CalendarDays}
                            title={year.nomor}
                        >
                            <div
                                className="absolute -top-6 -right-6 z-20"
                                ref={
                                    isDropdownOpen === year.nomor
                                        ? dropdownRef
                                        : null
                                }
                            >
                                <ButtonRounded
                                    size="sm"
                                    variant="icon"
                                    onClick={(e) => {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        setIsDropdownOpen(
                                            isDropdownOpen === year.nomor
                                                ? null
                                                : year.nomor
                                        );
                                    }}
                                >
                                    <IoIosMore size={16} />
                                </ButtonRounded>
                                <AnimatePresence>
                                    {isDropdownOpen === year.nomor && (
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
                                                    className={`block w-full text-left p-3 text-sm rounded-md cursor-pointer ${
                                                        downloadingStatus[
                                                            `${year.nomor}-excel`
                                                        ]
                                                            ? "bg-sky-50 text-sky-600"
                                                            : "text-neutral-700 hover:bg-sky-50 hover:text-sky-600"
                                                    }`}
                                                    onClick={async (e) => {
                                                        e.preventDefault();
                                                        e.stopPropagation();
                                                        await handleExportYear(
                                                            selectedClass.kelas,
                                                            selectedClass.jurusan,
                                                            year.nomor,
                                                            "excel"
                                                        );
                                                        setIsDropdownOpen(null);
                                                    }}
                                                >
                                                    {downloadingStatus[
                                                        `${year.nomor}-excel`
                                                    ] ? (
                                                        <span className="flex items-center gap-1">
                                                            <Loader2 className="w-5 h-5 animate-spin" />
                                                            Mengekspor...
                                                        </span>
                                                    ) : (
                                                        <span className="flex items-center gap-1">
                                                            <RiFileExcel2Line className="w-5 h-5" />
                                                            Export Excel
                                                        </span>
                                                    )}
                                                </div>
                                                <div
                                                    className={`block w-full text-left p-3 text-sm rounded-md cursor-pointer ${
                                                        downloadingStatus[
                                                            `${year.nomor}-pdf`
                                                        ]
                                                            ? "bg-sky-50 text-sky-600"
                                                            : "text-neutral-700 hover:bg-sky-50 hover:text-sky-600"
                                                    }`}
                                                    onClick={async (e) => {
                                                        e.preventDefault();
                                                        e.stopPropagation();
                                                        await handleExportYear(
                                                            selectedClass.kelas,
                                                            selectedClass.jurusan,
                                                            year.nomor,
                                                            "pdf"
                                                        );
                                                        setIsDropdownOpen(null);
                                                    }}
                                                >
                                                    {downloadingStatus[
                                                        `${year.nomor}-pdf`
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
            ) : (
                <DataNotFound
                    title="Data Tahun Ajaran Kosong"
                    message="Tidak ditemukan data tahun ajaran. Silakan tambahkan tahun ajaran terlebih dahulu."
                />
            )}
            <div className="flex justify-start mt-8">
                <ButtonRounded
                    as="link"
                    variant="outline"
                    href={route("absensi.index")}
                >
                    <ArrowLeft size={16} className="mr-2" />
                    Kembali
                </ButtonRounded>
            </div>
        </PageContent>
    );
};

SelectYearPage.layout = (page) => (
    <MainLayout children={page} title="Pilih Tahun" />
);

export default SelectYearPage;
