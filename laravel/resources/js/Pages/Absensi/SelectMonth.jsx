import { useEffect, useRef, useState } from "react";
import MainLayout from "@/Layouts/MainLayout";
import PageContent from "@/Components/common/PageContent";
import ContentCard from "@/Components/common/ContentCard";
import Button from "@/Components/common/Button";
import { usePage } from "@inertiajs/react";
import toast from "react-hot-toast";
import { AnimatePresence, motion } from "framer-motion";
import { ArrowLeft, CalendarDays, Loader2 } from "lucide-react";
import { RiFileExcel2Line } from "react-icons/ri";
import { IoIosMore } from "react-icons/io";
import { FaFilePdf } from "react-icons/fa6";

const SelectMonth = ({ months, tahun, selectedClass }) => {
    const { flash } = usePage().props;

    const [downloadingStatus, setDownloadingStatus] = useState({});
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

    const handleExport = async (monthSlug, format) => {
        const key = `${monthSlug}-${format}`;

        if (downloadingStatus[key]) {
            return;
        }

        setDownloadingStatus((prev) => ({ ...prev, [key]: true }));

        const url = route(`absensi.month.export.${format}`, {
            kelas: selectedClass.kelas,
            jurusan: selectedClass.jurusan,
            tahun: tahun,
            bulanSlug: monthSlug,
        });

        try {
            const response = await fetch(url);
            const contentType = response.headers.get("Content-Type");

            if (contentType && contentType.includes("application/json")) {
                const errorData = await response.json();
                toast.error(errorData.error);
            } else if (response.ok) {
                const blob = await response.blob();
                const downloadUrl = window.URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = downloadUrl;

                const contentDisposition = response.headers.get(
                    "Content-Disposition"
                );
                const filenameMatch =
                    contentDisposition &&
                    contentDisposition.match(/filename="([^"]+)"/);
                a.download = filenameMatch
                    ? filenameMatch[1]
                    : `Absensi-${monthSlug}-${tahun}.${
                          format === "excel" ? "xlsx" : "pdf"
                      }`;

                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(downloadUrl);
                toast.success("File berhasil diunduh!");
            } else {
                toast.error("Gagal mengekspor data. Terjadi kesalahan server.");
            }
        } catch (error) {
            console.error("Error during export:", error);
            toast.error("Gagal mengekspor data. Terjadi kesalahan.");
        } finally {
            setDownloadingStatus((prev) => ({ ...prev, [key]: false }));
            setIsDropdownOpen(null);
        }
    };

    const dropdownVariants = {
        hidden: { opacity: 0, y: -5, scale: 0.98 },
        visible: { opacity: 1, y: 0, scale: 1 },
    };

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
        <PageContent
            breadcrumbItems={breadcrumbItems}
            pageClassName="-mt-16 md:-mt-20"
        >
            <h3 className="text-md md:text-lg font-medium text-neutral-700 mb-4 md:mb-6">
                Pilih Bulan ({tahun})
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                {months.map((month) => (
                    <ContentCard
                        key={month.slug}
                        href={route("absensi.month.show", {
                            kelas: selectedClass.kelas,
                            jurusan: selectedClass.jurusan,
                            tahun: tahun,
                            bulanSlug: month.slug,
                        })}
                        icon={CalendarDays}
                        title={month.nama}
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
                                        variants={dropdownVariants}
                                        transition={{
                                            duration: 0.15,
                                            ease: "easeInOut",
                                        }}
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
                    </ContentCard>
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

SelectMonth.layout = (page) => (
    <MainLayout children={page} title={`Pilih Bulan - ${page.props.tahun}`} />
);

export default SelectMonth;
