import { AnimatePresence, motion } from "framer-motion";
import { CheckCircle } from "lucide-react";
import { IoIosMore } from "react-icons/io";
import { TbCalendarCheck } from "react-icons/tb";
import Button from "@/Components/common/Button";
import ContentCard from "@/Components/common/ContentCard";

const SelectDayDesktop = ({
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

    return (
        <div className="hidden md:block">
            <div className="grid grid-cols-7 gap-4">
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
                        {dayName}
                    </div>
                ))}

                {days.map((day, index) => {
                    if (day.is_outside_month) {
                        return (
                            <div
                                key={`outside-${day.nomor}-${index}`}
                                className="flex flex-col items-center justify-center rounded-lg h-24 relative bg-slate-50 text-neutral-400 cursor-not-allowed"
                            >
                                <span className="text-xl font-semibold">
                                    {day.nomor}
                                </span>
                                <span className="text-xs text-neutral-400">
                                    {day.nama_hari}
                                </span>
                            </div>
                        );
                    }

                    return (
                        <ContentCard
                            key={`inside-${day.nomor}`}
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
                            variant={
                                hasAbsensi(day.nomor)
                                    ? "success"
                                    : isHoliday(day.nomor)
                                    ? "error"
                                    : "default"
                            }
                            title={day.nomor}
                            subtitle={day.nama_hari}
                        >
                            {day.nomor && (
                                <>
                                    {hasAbsensi(day.nomor) && (
                                        <div className="absolute -top-4 -right-3">
                                            <CheckCircle className="w-5 h-5 text-green-600" />
                                        </div>
                                    )}
                                    {isHoliday(day.nomor) && (
                                        <div className="absolute -top-4 -right-4 text-sm font-semibold text-red-600">
                                            LIBUR
                                        </div>
                                    )}
                                    {!isHoliday(day.nomor) &&
                                        !hasAbsensi(day.nomor) && (
                                            <div
                                                className="absolute -top-6 -right-6 z-20"
                                                ref={
                                                    isDropdownOpen === day.nomor
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
                                                            isDropdownOpen ===
                                                                day.nomor
                                                                ? null
                                                                : day.nomor
                                                        );
                                                    }}
                                                >
                                                    <IoIosMore size={16} />
                                                </Button>
                                                <AnimatePresence>
                                                    {isDropdownOpen ===
                                                        day.nomor && (
                                                        <motion.div
                                                            initial="hidden"
                                                            animate="visible"
                                                            exit="hidden"
                                                            variants={
                                                                dropdownVariants
                                                            }
                                                            transition={{
                                                                duration: 0.15,
                                                                ease: "easeInOut",
                                                            }}
                                                            className="absolute right-0 top-8 w-48 rounded-lg shadow-lg bg-white border border-slate-200"
                                                        >
                                                            <div className="px-1 py-3">
                                                                <div
                                                                    className="block w-full text-left p-3 text-sm rounded-md cursor-pointer text-neutral-700 hover:bg-sky-50 hover:text-sky-600"
                                                                    onClick={(
                                                                        e
                                                                    ) => {
                                                                        e.preventDefault();
                                                                        e.stopPropagation();
                                                                        handleSetHoliday(
                                                                            day.nomor
                                                                        );
                                                                    }}
                                                                >
                                                                    <span className="flex items-center gap-1">
                                                                        <TbCalendarCheck className="w-4 h-4 inline-block" />
                                                                        Tetapkan
                                                                        Hari
                                                                        Libur
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </motion.div>
                                                    )}
                                                </AnimatePresence>
                                            </div>
                                        )}
                                </>
                            )}
                        </ContentCard>
                    );
                })}
            </div>
        </div>
    );
};

export default SelectDayDesktop;
