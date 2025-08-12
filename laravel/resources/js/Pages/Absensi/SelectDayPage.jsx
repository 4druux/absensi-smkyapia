import { useEffect } from "react";

import toast from "react-hot-toast";
import { usePage } from "@inertiajs/react";
import { ArrowLeft } from "lucide-react";

// Components
import MainLayout from "@/Layouts/MainLayout";
import ButtonRounded from "@/Components/common/button-rounded";
import DotLoader from "@/Components/ui/dot-loader";
import PageContent from "@/Components/ui/page-content";
import SelectDayDesktop from "@/Components/absensi/select-day-desktop";
import SelectDayMobile from "@/Components/absensi/select-day-mobile";
import { useAbsensiDays } from "@/hooks/absensi/use-absensi-days";
import { useDropdown, dropdownAnimation } from "@/hooks/use-dropdown";

const SelectDayPage = ({ tahun, bulan, namaBulan, selectedClass }) => {
    const { flash } = usePage().props;
    const { isOpen, setIsOpen, dropdownRef } = useDropdown();

    const { days, absensiDays, holidays, isLoading, error, handleSetHoliday } =
        useAbsensiDays(
            selectedClass.kelas,
            selectedClass.jurusan,
            tahun,
            bulan
        );

    useEffect(() => {
        if (flash && flash.success) {
            toast.success(flash.success);
        }
        if (flash && flash.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    let displayYear;
    const [startYear, endYear] = tahun.split("-");
    const month = namaBulan.toLowerCase();

    if (
        month === "januari" ||
        month === "februari" ||
        month === "maret" ||
        month === "april" ||
        month === "mei" ||
        month === "juni"
    ) {
        displayYear = endYear;
    } else {
        displayYear = startYear;
    }

    const breadcrumbItems = [
        { label: "Absensi", href: route("absensi.index") },
        {
            label: `${selectedClass.kelas} ${selectedClass.kelompok} - ${selectedClass.jurusan}`,
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

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-screen">
                <DotLoader text="Memuat data kalender..." />
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex items-center justify-center h-screen">
                Gagal memuat data kalender.
            </div>
        );
    }

    const selectDataProps = {
        days,
        absensiDays,
        holidays,
        selectedClass,
        tahun,
        bulan,
        handleSetHoliday,
        isOpen,
        setIsOpen,
        dropdownRef,
        dropdownAnimation,
    };

    return (
        <PageContent
            breadcrumbItems={breadcrumbItems}
            pageClassName="-mt-16 md:-mt-20"
        >
            <h3 className="text-md md:text-lg font-medium text-neutral-700 mb-4 md:mb-6">
                Pilih Hari ({namaBulan} {displayYear})
            </h3>

            <SelectDayMobile {...selectDataProps} />
            <SelectDayDesktop {...selectDataProps} />

            <div className="flex justify-start mt-8">
                <ButtonRounded
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
                </ButtonRounded>
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
