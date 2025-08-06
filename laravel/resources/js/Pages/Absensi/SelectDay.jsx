import { useEffect, useRef, useState } from "react";
import MainLayout from "@/Layouts/MainLayout";
import { ArrowLeft } from "lucide-react";
import Button from "@/Components/common/Button";
import PageContent from "@/Components/common/PageContent";
import { usePage, router } from "@inertiajs/react";
import toast from "react-hot-toast";
import SelectDayMobile from "@/Components/absensi/SelectDayMobile";
import SelectDayDesktop from "@/Components/absensi/SelectDayDesktop";

const SelectDayPage = ({
    tahun,
    bulan,
    namaBulan,
    days,
    absensiDays,
    holidays,
    selectedClass,
}) => {
    const { flash } = usePage().props;
    const [isDropdownOpen, setIsDropdownOpen] = useState(null);
    const dropdownRef = useRef(null);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (
                isDropdownOpen &&
                dropdownRef.current &&
                !dropdownRef.current.contains(event.target)
            ) {
                setIsDropdownOpen(null);
            }
        };

        if (isDropdownOpen) {
            document.addEventListener("mousedown", handleClickOutside);
        }

        return () => {
            document.removeEventListener("mousedown", handleClickOutside);
        };
    }, [isDropdownOpen]);

    useEffect(() => {
        if (flash && flash.success) {
            toast.success(flash.success);
        }
        if (flash && flash.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    const hasAbsensi = (day) => {
        return absensiDays.includes(day);
    };

    const isHoliday = (day) => {
        return holidays.includes(day);
    };

    const handleSetHoliday = (dayNumber) => {
        if (isHoliday(dayNumber)) {
            toast.error("Tanggal ini sudah ditetapkan sebagai hari libur.");
            setIsDropdownOpen(null);
            return;
        }

        const confirmSetHoliday = confirm(
            `Apakah Anda yakin ingin menetapkan tanggal ${dayNumber} sebagai hari libur?`
        );

        if (confirmSetHoliday) {
            router.post(
                route("absensi.holiday.store", {
                    kelas: selectedClass.kelas,
                    jurusan: selectedClass.jurusan,
                    tahun: tahun,
                    bulanSlug: bulan,
                    tanggal: dayNumber,
                }),
                {},
                {
                    onSuccess: () => {
                        toast.success(
                            `Tanggal ${dayNumber} berhasil ditetapkan sebagai hari libur.`
                        );
                        setIsDropdownOpen(null);
                    },
                    onError: () => {
                        toast.error("Gagal menetapkan hari libur.");
                    },
                }
            );
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

    return (
        <PageContent
            breadcrumbItems={breadcrumbItems}
            pageClassName="-mt-16 md:-mt-20"
        >
            <h3 className="text-md md:text-lg font-medium text-neutral-700 mb-4 md:mb-6">
                Pilih Hari ({namaBulan} {tahun})
            </h3>

            <SelectDayMobile
                days={days}
                absensiDays={absensiDays}
                holidays={holidays}
                selectedClass={selectedClass}
                tahun={tahun}
                bulan={bulan}
                handleSetHoliday={handleSetHoliday}
                isDropdownOpen={isDropdownOpen}
                setIsDropdownOpen={setIsDropdownOpen}
                dropdownRef={dropdownRef}
                dropdownVariants={dropdownVariants}
            />
            <SelectDayDesktop
                days={days}
                absensiDays={absensiDays}
                holidays={holidays}
                selectedClass={selectedClass}
                tahun={tahun}
                bulan={bulan}
                namaBulan={namaBulan}
                handleSetHoliday={handleSetHoliday}
                isDropdownOpen={isDropdownOpen}
                setIsDropdownOpen={setIsDropdownOpen}
                dropdownRef={dropdownRef}
                dropdownVariants={dropdownVariants}
            />

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
        </PageContent>
    );
};

SelectDayPage.layout = (page) => (
    <MainLayout
        children={page}
        title={`Pilih Tanggal - ${page.props.namaBulan}`}
    />
);

export default SelectDayPage;
