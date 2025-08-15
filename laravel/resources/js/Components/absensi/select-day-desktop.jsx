import { AnimatePresence, motion } from "framer-motion";
import { CheckCircle } from "lucide-react";
import { IoIosMore } from "react-icons/io";
import { TbCalendarCheck } from "react-icons/tb";
import ButtonRounded from "@/Components/common/button-rounded";
import CardContent from "@/Components/ui/card-content";

const SelectDayDesktop = ({
    days,
    absensiDays,
    holidays,
    selectedClass,
    tahun,
    bulan,
    handleSetHoliday,
    handleCancelHoliday,
    isOpen,
    setIsOpen,
    dropdownRef,
    dropdownAnimation,
}) => {
    const hasAbsensi = (day) => {
        return absensiDays.includes(day);
    };

    const isHoliday = (day) => {
        return Array.isArray(holidays) && holidays.includes(day.nomor);
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

                    const isCurrentDayHoliday = isHoliday(day);
                    const hasAbsensiData = hasAbsensi(day.nomor);
                    const cardVariant = hasAbsensiData
                        ? "success"
                        : isCurrentDayHoliday
                        ? "error"
                        : "default";
                    const cardHref = isCurrentDayHoliday
                        ? null
                        : route("absensi.day.show", {
                              kelas: selectedClass.kelas,
                              jurusan: selectedClass.jurusan,
                              tahun,
                              bulanSlug: bulan,
                              tanggal: day.nomor,
                          });

                    return (
                        <CardContent
                            key={`inside-${day.nomor}`}
                            href={cardHref}
                            variant={cardVariant}
                            title={day.nomor}
                            subtitle={day.nama_hari}
                        >
                            {isCurrentDayHoliday && (
                                <div className="absolute -top-4 -left-3 text-xs font-semibold text-red-600">
                                    LIBUR
                                </div>
                            )}
                            {hasAbsensiData && (
                                <div className="absolute -top-4 -right-3">
                                    <CheckCircle className="w-5 h-5 text-green-600" />
                                </div>
                            )}

                            {isCurrentDayHoliday && !hasAbsensiData && (
                                <div
                                    className="absolute -top-6 -right-6 z-20"
                                    ref={
                                        isOpen === day.nomor
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
                                            setIsOpen(
                                                !isOpen === day.nomor
                                                    ? null
                                                    : day.nomor
                                            );
                                        }}
                                    >
                                        <IoIosMore className="w-4 h-4" />
                                    </ButtonRounded>
                                    <AnimatePresence>
                                        {isOpen === day.nomor && (
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
                                                <div className="px-1 py-3">
                                                    <div
                                                        className="block w-full text-left p-3 text-sm rounded-md cursor-pointer text-neutral-700 hover:bg-sky-50 hover:text-sky-600"
                                                        onClick={(e) => {
                                                            e.preventDefault();
                                                            e.stopPropagation();
                                                            handleCancelHoliday(
                                                                day.nomor
                                                            );
                                                            setIsOpen(null);
                                                        }}
                                                    >
                                                        <span className="flex items-center gap-1">
                                                            <TbCalendarCheck className="w-4 h-4 inline-block" />
                                                            Batalkan Hari Libur
                                                        </span>
                                                    </div>
                                                </div>
                                            </motion.div>
                                        )}
                                    </AnimatePresence>
                                </div>
                            )}
                            {!isCurrentDayHoliday && !hasAbsensiData && (
                                <div
                                    className="absolute -top-6 -right-6 z-20"
                                    ref={
                                        isOpen === day.nomor
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
                                            setIsOpen(
                                                !isOpen === day.nomor
                                                    ? null
                                                    : day.nomor
                                            );
                                        }}
                                    >
                                        <IoIosMore className="w-4 h-4" />
                                    </ButtonRounded>
                                    <AnimatePresence>
                                        {isOpen === day.nomor && (
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
                                                <div className="px-1 py-3">
                                                    <div
                                                        className="block w-full text-left p-3 text-sm rounded-md cursor-pointer text-neutral-700 hover:bg-sky-50 hover:text-sky-600"
                                                        onClick={(e) => {
                                                            e.preventDefault();
                                                            e.stopPropagation();
                                                            handleSetHoliday(
                                                                day.nomor
                                                            );
                                                            setIsOpen(null);
                                                        }}
                                                    >
                                                        <span className="flex items-center gap-1">
                                                            <TbCalendarCheck className="w-4 h-4 inline-block" />
                                                            Tetapkan Hari Libur
                                                        </span>
                                                    </div>
                                                </div>
                                            </motion.div>
                                        )}
                                    </AnimatePresence>
                                </div>
                            )}
                        </CardContent>
                    );
                })}
            </div>
        </div>
    );
};

export default SelectDayDesktop;
