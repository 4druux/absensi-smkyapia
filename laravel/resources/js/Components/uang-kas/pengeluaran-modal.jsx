import { useEffect } from "react";
import { AnimatePresence, motion } from "framer-motion";
import { Loader2, Save, X } from "lucide-react";

import ButtonRounded from "@/Components/common/button-rounded";
import { usePengeluaranForm } from "@/hooks/uang-kas/use-pengeluaran-form";
import { formatRupiah } from "@/utils/formatRupiah";

const PengeluaranModal = ({
    isOpen,
    onClose,
    selectedClass,
    onSuccess,
    displayYear,
    bulanSlug,
}) => {
    const {
        formData,
        errors,
        isSubmitting,
        handleFormChange,
        handleSubmit,
        resetForm,
    } = usePengeluaranForm();

    useEffect(() => {
        if (!isOpen) {
            resetForm();
        }
    }, [isOpen]);

    useEffect(() => {
        if (isOpen) {
            document.body.style.overflow = "hidden";
        } else {
            document.body.style.overflow = "unset";
        }

        return () => {
            document.body.style.overflow = "unset";
        };
    }, [isOpen]);

    const submit = (e) => {
        handleSubmit(e, {
            kelas: selectedClass.kelas,
            jurusan: selectedClass.jurusan,
            displayYear,
            bulanSlug,
            onClose,
            onSuccess,
        });
    };

    return (
        <AnimatePresence>
            {isOpen && (
                <motion.div
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    onClick={onClose}
                    className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
                >
                    <motion.div
                        initial={{ y: "100%" }}
                        animate={{ y: 0 }}
                        exit={{ y: "100%" }}
                        transition={{
                            type: "spring",
                            damping: 25,
                            stiffness: 250,
                        }}
                        onClick={(e) => e.stopPropagation()}
                        className="fixed bottom-0 left-0 right-0 bg-white rounded-t-2xl shadow-xl w-full max-h-[85dvh] overflow-y-auto md:max-w-2xl md:static md:rounded-2xl md:max-h-[100%]"
                    >
                        <div className="md:hidden py-4 flex justify-center sticky top-0 bg-white z-10">
                            <div className="w-16 h-1 bg-neutral-300 rounded-full" />
                        </div>
                        <form onSubmit={submit}>
                            <div className="px-4 border-b border-slate-300 pb-4 md:p-4">
                                <div className="flex justify-between items-center">
                                    <h3 className="text-lg font-medium text-neutral-700">
                                        Ajukan Pengeluaran Kas
                                    </h3>
                                    <div
                                        onClick={onClose}
                                        className="p-2 hover:bg-slate-50 rounded-full group cursor-pointer"
                                    >
                                        <X className="w-5 h-5 text-neutral-500 group-hover:text-neutral-800 group-hover:rotate-120 transition-all duration-300" />
                                    </div>
                                </div>
                            </div>
                            <div className="p-4 md:p-6 flex flex-col space-y-4 md:space-y-6">
                                <div>
                                    <label className="block text-sm font-medium text-neutral-700">
                                        Tanggal
                                    </label>
                                    <input
                                        type="date"
                                        value={formData.tanggal}
                                        onChange={(e) =>
                                            handleFormChange(
                                                "tanggal",
                                                e.target.value
                                            )
                                        }
                                        className={`mt-1 w-full px-3 py-2 rounded-xl border focus:outline-none placeholder:text-sm ${
                                            errors.tanggal
                                                ? "border-red-500"
                                                : "border-slate-300 focus:border-sky-500"
                                        }`}
                                    />
                                    {errors.tanggal && (
                                        <p className="text-xs text-red-500 mt-1">
                                            {errors.tanggal[0]}
                                        </p>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-neutral-700">
                                        Keperluan / Deskripsi
                                    </label>
                                    <input
                                        value={formData.deskripsi}
                                        onChange={(e) =>
                                            handleFormChange(
                                                "deskripsi",
                                                e.target.value
                                            )
                                        }
                                        placeholder="Misalnya: Beli spidol papan tulis"
                                        className={`mt-1 w-full px-3 py-2 rounded-xl border focus:outline-none placeholder:text-sm ${
                                            errors.deskripsi
                                                ? "border-red-500"
                                                : "border-slate-300 focus:border-sky-500"
                                        }`}
                                    />
                                    {errors.deskripsi && (
                                        <p className="text-xs text-red-500 mt-1">
                                            {errors.deskripsi[0]}
                                        </p>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-neutral-700">
                                        Nominal Pengeluaran
                                    </label>
                                    <input
                                        type="text"
                                        value={formatRupiah(formData.nominal)}
                                        onChange={(e) => {
                                            const rawValue =
                                                e.target.value.replace(
                                                    /[^0-9]/g,
                                                    ""
                                                );
                                            handleFormChange(
                                                "nominal",
                                                rawValue
                                            );
                                        }}
                                        placeholder="Contoh: Rp 50.000"
                                        className={`mt-1 w-full px-3 py-2 rounded-xl border focus:outline-none placeholder:text-sm ${
                                            errors.nominal
                                                ? "border-red-500"
                                                : "border-slate-300 focus:border-sky-500"
                                        }`}
                                    />
                                    {errors.nominal && (
                                        <p className="text-xs text-red-500 mt-1">
                                            {errors.nominal[0]}
                                        </p>
                                    )}
                                </div>
                                <div className="flex justify-end">
                                    <ButtonRounded
                                        type="submit"
                                        variant="primary"
                                        disabled={isSubmitting}
                                    >
                                        {isSubmitting && (
                                            <Loader2 className="animate-spin w-4 h-4 mr-2" />
                                        )}
                                        <Save className="w-4 h-4 mr-2" />
                                        Ajukan
                                    </ButtonRounded>
                                </div>
                            </div>
                        </form>
                    </motion.div>
                </motion.div>
            )}
        </AnimatePresence>
    );
};

export default PengeluaranModal;
