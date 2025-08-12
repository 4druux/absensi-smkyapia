import { useEffect } from "react";
import { AnimatePresence, motion } from "framer-motion";
import { Loader2, Save, X } from "lucide-react";
import ButtonRounded from "@/Components/common/button-rounded";
import Select from "@/Components/common/select";
import { usePermasalahanForm } from "@/hooks/permasalahan/use-permasalahan-form";


const ProblemModal = ({
    isOpen,
    onClose,
    isStudentProblem,
    students,
    selectedClass,
    tahun,
}) => {
    const {
        formData,
        isSubmitting,
        errors,
        handleFormChange,
        handleSubmit,
        resetForm,
    } = usePermasalahanForm();

    useEffect(() => {
        if (!isOpen) {
            resetForm();
        }
    }, [isOpen]);

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
                        <form
                            onSubmit={(e) =>
                                handleSubmit(e, {
                                    isStudentProblem,
                                    kelas_id: selectedClass.id,
                                    tahun,
                                    onClose,
                                })
                            }
                        >
                            <div className="px-4 md:p-6 border-b border-slate-300 pb-4 md:pb-0">
                                <div className="flex justify-between items-center">
                                    <h3 className="text-lg font-medium text-neutral-700">
                                        {isStudentProblem
                                            ? "Tambah Laporan Siswa"
                                            : "Tambah Laporan Kelas"}
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
                                        Tgl/Bln/Tahun
                                    </label>
                                    <input
                                        type="date"
                                        id="tanggal"
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
                                            {errors.tanggal}
                                        </p>
                                    )}
                                </div>

                                {isStudentProblem && (
                                    <div>
                                        <Select
                                            label="Nama Siswa"
                                            title="Nama Siswa"
                                            options={students.map((s) => ({
                                                value: s.id,
                                                label: s.nama,
                                            }))}
                                            value={formData.siswa_id}
                                            onChange={(value) =>
                                                handleFormChange(
                                                    "siswa_id",
                                                    value
                                                )
                                            }
                                            placeholder="-- Pilih Siswa --"
                                            error={errors.siswa_id}
                                        />
                                    </div>
                                )}

                                <div className="md:col-span-2">
                                    <label className="block text-sm font-medium text-neutral-700">
                                        Masalah
                                    </label>
                                    <textarea
                                        id="masalah"
                                        rows="3"
                                        value={formData.masalah}
                                        onChange={(e) =>
                                            handleFormChange(
                                                "masalah",
                                                e.target.value
                                            )
                                        }
                                        placeholder="Masalah yang dialami..."
                                        className={`mt-1 w-full px-3 py-2 rounded-xl border focus:outline-none placeholder:text-sm min-h-[100px] max-h-[150px] ${
                                            errors.masalah
                                                ? "border-red-500"
                                                : "border-slate-300 focus:border-sky-500"
                                        }`}
                                    />
                                    {errors.masalah && (
                                        <p className="text-xs text-red-500 mt-1">
                                            {errors.masalah}
                                        </p>
                                    )}
                                </div>

                                {isStudentProblem ? (
                                    <div className="md:col-span-2">
                                        <label className="block text-sm font-medium text-neutral-700">
                                            Tindakan Wali Kelas
                                        </label>
                                        <textarea
                                            id="tindakan_walas"
                                            rows="3"
                                            value={formData.tindakan_walas}
                                            onChange={(e) =>
                                                handleFormChange(
                                                    "tindakan_walas",
                                                    e.target.value
                                                )
                                            }
                                            placeholder="Tindakan Wali Kelas..."
                                            className={`mt-1 w-full px-3 py-2 rounded-xl border focus:outline-none placeholder:text-sm min-h-[100px] max-h-[150px] ${
                                                errors.tindakan_walas
                                                    ? "border-red-500"
                                                    : "border-slate-300 focus:border-sky-500"
                                            }`}
                                        />
                                        {errors.tindakan_walas && (
                                            <p className="text-xs text-red-500 mt-1">
                                                {errors.tindakan_walas}
                                            </p>
                                        )}
                                    </div>
                                ) : (
                                    <div className="md:col-span-2">
                                        <label
                                            htmlFor="pemecahan"
                                            className="block text-sm font-medium text-neutral-700"
                                        >
                                            Pemecahan Masalah
                                        </label>
                                        <textarea
                                            id="pemecahan"
                                            rows="3"
                                            value={formData.pemecahan}
                                            onChange={(e) =>
                                                handleFormChange(
                                                    "pemecahan",
                                                    e.target.value
                                                )
                                            }
                                            placeholder="Pemecahan Masalah..."
                                            className={`mt-1 w-full px-3 py-2 rounded-xl border focus:outline-none placeholder:text-sm min-h-[100px] max-h-[150px] ${
                                                errors.pemecahan
                                                    ? "border-red-500"
                                                    : "border-slate-300 focus:border-sky-500"
                                            }`}
                                        ></textarea>
                                        {errors.pemecahan && (
                                            <p className="text-xs text-red-500 mt-1">
                                                {errors.pemecahan}
                                            </p>
                                        )}
                                    </div>
                                )}
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
                                        Simpan
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

export default ProblemModal;