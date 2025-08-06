import { AnimatePresence, motion } from "framer-motion";
import { CheckCircle } from "lucide-react";
import { IoIosMore } from "react-icons/io";
import { TbCalendarCheck } from "react-icons/tb";
import Button from "@/Components/common/Button";

const SelectDayMobile = ({
    days,
    absensiDays,
    holidays,
    selectedClass,
    tahun,
    bulan,
    handleSetHoliday,
    isDropdownOpen,
    setIsDropdownOpen,
    dropdownRef,
    dropdownVariants,
}) => {
    const hasAbsensi = (day) => {
        return absensiDays.includes(day);
    };

    const isHoliday = (day) => {
        return holidays.includes(day);
    };

    const getShortDayName = (day) => {
        if (!day) return "";
        const shortNames = {
            Minggu: "M",
            Senin: "S",
            Selasa: "S",
            Rabu: "R",
            Kamis: "K",
            Jumat: "J",
            Sabtu: "S",
        };
        return shortNames[day] || "";
    };

    return (
        <div className="md:hidden">
            <div className="grid grid-cols-7 gap-2">
                {[
                    "Minggu",
                    "Senin",
                    "Selasa",
                    "Rabu",
                    "Kamis",
                    "Jumat",
                    "Sabtu",
                ].map((dayName) => (
                    <div
                        key={dayName}
                        className="text-center font-semibold text-sm text-neutral-500"
                    >
                        {getShortDayName(dayName)}
                    </div>
                ))}
                {days.map((day, index) => {
                    if (day.is_outside_month) {
                        return (
                            <div
                                key={`outside-${day.nomor}-${index}`}
                                className="flex flex-col items-center justify-center rounded-lg h-16 w-full relative bg-slate-50 text-neutral-400 cursor-not-allowed"
                            >
                                <span className="font-semibold text-sm">
                                    {day.nomor}
                                </span>
                            </div>
                        );
                    }

                    return (
                        <div
                            key={`inside-${day.nomor}`}
                            className={`flex flex-col items-center justify-center rounded-lg h-16 w-full relative border
                                ${
                                    hasAbsensi(day.nomor)
                                        ? "bg-green-100 text-green-600 border-green-300"
                                        : isHoliday(day.nomor)
                                        ? "bg-red-100 text-red-600 border-red-300"
                                        : "bg-slate-100 text-neutral-700 border-slate-200"
                                }
                            `}
                        >
                            <a
                                href={
                                    isHoliday(day.nomor)
                                        ? null
                                        : route("absensi.day.show", {
                                              kelas: selectedClass.kelas,
                                              jurusan: selectedClass.jurusan,
                                              tahun,
                                              bulanSlug: bulan,
                                              tanggal: day.nomor,
                                          })
                                }
                                className="h-full w-full flex flex-col justify-center items-center"
                            >
                                <span className="font-semibold text-sm">
                                    {day.nomor}
                                </span>
                            </a>
                            {!isHoliday(day.nomor) &&
                                !hasAbsensi(day.nomor) && (
                                    <div className="absolute -top-2 -right-3 z-20">
                                        <Button
                                            size="sm"
                                            variant="icon"
                                            onClick={(e) => {
                                                e.preventDefault();
                                                e.stopPropagation();
                                                setIsDropdownOpen(
                                                    isDropdownOpen === day.nomor
                                                        ? null
                                                        : day.nomor
                                                );
                                            }}
                                        >
                                            <IoIosMore size={16} />
                                        </Button>
                                        <AnimatePresence>
                                            {isDropdownOpen === day.nomor && (
                                                <motion.div
                                                    initial="hidden"
                                                    animate="visible"
                                                    exit="hidden"
                                                    variants={dropdownVariants}
                                                    transition={{
                                                        duration: 0.15,
                                                        ease: "easeInOut",
                                                    }}
                                                    className="absolute -right-4 top-6 w-32 rounded-lg shadow-lg bg-white border border-slate-200 z-50"
                                                    ref={
                                                        isDropdownOpen ===
                                                        day.nomor
                                                            ? dropdownRef
                                                            : null
                                                    }
                                                >
                                                    <div className="p-1">
                                                        <div
                                                            className="block w-full text-left p-2 text-xs rounded-md cursor-pointer text-neutral-700 hover:bg-sky-50 hover:text-sky-600"
                                                            onClick={(e) => {
                                                                e.preventDefault();
                                                                e.stopPropagation();
                                                                handleSetHoliday(
                                                                    day.nomor
                                                                );
                                                            }}
                                                        >
                                                            <span className="flex items-center gap-1">
                                                                <TbCalendarCheck className="w-4 h-4 inline-block" />
                                                                Hari Libur
                                                            </span>
                                                        </div>
                                                    </div>
                                                </motion.div>
                                            )}
                                        </AnimatePresence>
                                    </div>
                                )}
                            {hasAbsensi(day.nomor) && (
                                <div className="absolute top-1 right-1">
                                    <CheckCircle className="w-4 h-4 text-green-600" />
                                </div>
                            )}
                        </div>
                    );
                })}
            </div>
        </div>
    );
};

export default SelectDayMobile;
