import { useEffect } from "react";
import { AnimatePresence, motion } from "framer-motion";
import { Loader2, Save, X, PlusCircle, Trash2 } from "lucide-react";
import ButtonRounded from "@/Components/common/button-rounded";
import Select from "@/Components/common/select";
import { useIndisiplinerForm } from "@/hooks/indisipliner/use-indisipliner-form";

const IndisiplinerModal = ({
    isOpen,
    onClose,
    students,
    selectedClass,
    tahun,
    mutate,
}) => {
    const {
        formData,
        isSubmitting,
        errors,
        handleFormChange,
        handleSubmit,
        resetForm,
        setFormData,
    } = useIndisiplinerForm();

    useEffect(() => {
        if (!isOpen) {
            resetForm();
        }
    }, [isOpen, resetForm]);

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

    const suratOptions = [
        { value: "Surat Peringatan 1", label: "Surat Peringatan 1" },
        { value: "Surat Peringatan 2", label: "Surat Peringatan 2" },
        { value: "Surat Peringatan 3", label: "Surat Peringatan 3" },
        { value: "Surat Pernyataan 3", label: "Surat Pernyataan 3" },
        { value: "Surat Pernyataan 2", label: "Surat Pernyataan 2" },
        { value: "Surat Pernyataan 1", label: "Surat Pernyataan 1" },
        { value: "Surat Komitmen 3", label: "Surat Komitmen 3" },
        { value: "Surat Komitmen 2", label: "Surat Komitmen 2" },
        { value: "Surat Komitmen 1", label: "Surat Komitmen 1" },
        {
            value: "Motivasi dan Teguran Walas",
            label: "Motivasi dan Teguran Walas",
        },
    ];

    const handleOtherIndisiplinerChange = (index, field, value) => {
        const newDetails = [...formData.details];
        newDetails[index][field] = value;
        setFormData((prev) => ({
            ...prev,
            details: newDetails,
        }));
    };

    const handleAddOtherIndisipliner = (e) => {
        e.preventDefault();
        setFormData((prev) => ({
            ...prev,
            details: [
                ...prev.details,
                { jenis_pelanggaran: "", alasan: "", poin: "" },
            ],
        }));
    };

    const handleRemoveOtherIndisipliner = (e, index) => {
        e.preventDefault();
        const newDetails = [...formData.details];
        newDetails.splice(index, 1);
        setFormData((prev) => ({
            ...prev,
            details: newDetails,
        }));
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
                        <form
                            onSubmit={(e) =>
                                handleSubmit(e, {
                                    selectedClassId: selectedClass.id,
                                    tahun,
                                    onClose,
                                    mutate,
                                })
                            }
                        >
                            <div className="px-4 border-b border-slate-300 pb-4 md:p-4">
                                <div className="flex justify-between items-center">
                                    <h3 className="text-lg font-medium text-neutral-700">
                                        Tambah Data Indisipliner
                                    </h3>

                                    <div
                                        onClick={onClose}
                                        className="p-2 hover:bg-slate-50 rounded-full group cursor-pointer"
                                    >
                                        <X className="w-5 h-5 text-neutral-500 group-hover:text-neutral-800 group-hover:rotate-120 transition-all duration-300" />
                                    </div>
                                </div>
                            </div>

                            <div className="p-4 md:p-6 grid grid-cols-1 md:grid-cols-2 md:gap-4">
                                <div>
                                    <Select
                                        label="Nama Siswa"
                                        title="Nama Siswa"
                                        options={students.map((s) => ({
                                            value: s.id,
                                            label: `${s.nama} (${s.nis})`,
                                        }))}
                                        value={formData.siswa_id}
                                        onChange={(value) =>
                                            handleFormChange("siswa_id", value)
                                        }
                                        placeholder="-- Pilih Siswa --"
                                        error={errors.siswa_id}
                                    />
                                </div>

                                <div className="mb-6 md:mb-0">
                                    <label className="block text-sm font-medium text-neutral-700">
                                        Tanggal Surat
                                    </label>
                                    <input
                                        type="date"
                                        id="tanggal_surat"
                                        value={formData.tanggal_surat}
                                        onChange={(e) =>
                                            handleFormChange(
                                                "tanggal_surat",
                                                e.target.value
                                            )
                                        }
                                        className={`mt-1 w-full px-3 py-2 rounded-xl border focus:outline-none placeholder:text-sm ${
                                            errors.tanggal_surat
                                                ? "border-red-500"
                                                : "border-slate-300 focus:border-sky-500"
                                        }`}
                                    />
                                    {errors.tanggal_surat && (
                                        <p className="text-xs text-red-500 mt-1">
                                            {errors.tanggal_surat}
                                        </p>
                                    )}
                                </div>

                                <div>
                                    <Select
                                        label="Jenis Surat"
                                        title="Jenis Surat"
                                        options={suratOptions}
                                        value={formData.jenis_surat}
                                        onChange={(value) =>
                                            handleFormChange(
                                                "jenis_surat",
                                                value
                                            )
                                        }
                                        placeholder="-- Pilih Jenis Surat --"
                                        error={errors.jenis_surat}
                                    />
                                </div>

                                <div className="mb-6 md:mb-0">
                                    <label className="block text-sm font-medium text-neutral-700">
                                        Nomor Surat
                                    </label>
                                    <input
                                        type="text"
                                        id="nomor_surat"
                                        value={formData.nomor_surat}
                                        onChange={(e) =>
                                            handleFormChange(
                                                "nomor_surat",
                                                e.target.value
                                            )
                                        }
                                        placeholder="Nomor Surat"
                                        className={`mt-1 w-full px-3 py-2 rounded-xl border focus:outline-none placeholder:text-sm ${
                                            errors.nomor_surat
                                                ? "border-red-500"
                                                : "border-slate-300 focus:border-sky-500"
                                        }`}
                                    />
                                    {errors.nomor_surat && (
                                        <p className="text-xs text-red-500 mt-1">
                                            {errors.nomor_surat}
                                        </p>
                                    )}
                                </div>

                                <div className="md:col-span-2">
                                    <h4 className="text-sm font-medium text-neutral-700 mb-4">
                                        Pelanggaran Utama
                                    </h4>

                                    <div className="flex flex-row items-start gap-2 mb-4">
                                        <div className="flex-1">
                                            <label className="block text-xs font-medium text-neutral-700">
                                                Alasan Keterlambatan
                                            </label>
                                            <input
                                                type="text"
                                                id="terlambat_alasan"
                                                value={
                                                    formData.terlambat_alasan
                                                }
                                                onChange={(e) =>
                                                    handleFormChange(
                                                        "terlambat_alasan",
                                                        e.target.value
                                                    )
                                                }
                                                placeholder="Alasan keterlambatan..."
                                                className={`mt-1 w-full px-3 py-2 rounded-xl border focus:outline-none placeholder:text-sm ${
                                                    errors.terlambat_alasan
                                                        ? "border-red-500"
                                                        : "border-slate-300 focus:border-sky-500"
                                                }`}
                                            />
                                            {errors.terlambat_alasan && (
                                                <p className="text-xs text-red-500 mt-1">
                                                    {errors.terlambat_alasan}
                                                </p>
                                            )}
                                        </div>
                                        <div className="w-20">
                                            <label className="block text-xs font-medium text-neutral-700">
                                                Poin
                                            </label>
                                            <input
                                                type="text"
                                                inputMode="numeric"
                                                id="terlambat_poin"
                                                value={formData.terlambat_poin}
                                                onChange={(e) =>
                                                    handleFormChange(
                                                        "terlambat_poin",
                                                        e.target.value.replace(
                                                            /[^0-9]/g,
                                                            ""
                                                        )
                                                    )
                                                }
                                                placeholder="Poin"
                                                className={`mt-1 w-full px-3 py-2 rounded-xl border focus:outline-none placeholder:text-sm ${
                                                    errors.terlambat_poin
                                                        ? "border-red-500"
                                                        : "border-slate-300 focus:border-sky-500"
                                                }`}
                                            />
                                            {errors.terlambat_poin && (
                                                <p className="text-xs text-red-500 mt-1">
                                                    {errors.terlambat_poin}
                                                </p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="flex flex-row items-start gap-2 mb-4">
                                        <div className="flex-1">
                                            <label className="block text-xs font-medium text-neutral-700">
                                                Alasan Alfa
                                            </label>
                                            <input
                                                type="text"
                                                id="alfa_alasan"
                                                value={formData.alfa_alasan}
                                                onChange={(e) =>
                                                    handleFormChange(
                                                        "alfa_alasan",
                                                        e.target.value
                                                    )
                                                }
                                                placeholder="Alasan alfa..."
                                                className={`mt-1 w-full px-3 py-2 rounded-xl border focus:outline-none placeholder:text-sm ${
                                                    errors.alfa_alasan
                                                        ? "border-red-500"
                                                        : "border-slate-300 focus:border-sky-500"
                                                }`}
                                            />
                                            {errors.alfa_alasan && (
                                                <p className="text-xs text-red-500 mt-1">
                                                    {errors.alfa_alasan}
                                                </p>
                                            )}
                                        </div>
                                        <div className="w-20">
                                            <label className="block text-xs font-medium text-neutral-700">
                                                Poin
                                            </label>
                                            <input
                                                type="text"
                                                inputMode="numeric"
                                                id="alfa_poin"
                                                value={formData.alfa_poin}
                                                onChange={(e) =>
                                                    handleFormChange(
                                                        "alfa_poin",
                                                        e.target.value.replace(
                                                            /[^0-9]/g,
                                                            ""
                                                        )
                                                    )
                                                }
                                                placeholder="Poin"
                                                className={`mt-1 w-full px-3 py-2 rounded-xl border focus:outline-none placeholder:text-sm ${
                                                    errors.alfa_poin
                                                        ? "border-red-500"
                                                        : "border-slate-300 focus:border-sky-500"
                                                }`}
                                            />
                                            {errors.alfa_poin && (
                                                <p className="text-xs text-red-500 mt-1">
                                                    {errors.alfa_poin}
                                                </p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="flex flex-row items-start gap-2 mb-4">
                                        <div className="flex-1">
                                            <label className="block text-xs font-medium text-neutral-700">
                                                Alasan Bolos
                                            </label>
                                            <input
                                                type="text"
                                                id="bolos_alasan"
                                                value={formData.bolos_alasan}
                                                onChange={(e) =>
                                                    handleFormChange(
                                                        "bolos_alasan",
                                                        e.target.value
                                                    )
                                                }
                                                placeholder="Alasan bolos..."
                                                className={`mt-1 w-full px-3 py-2 rounded-xl border focus:outline-none placeholder:text-sm ${
                                                    errors.bolos_alasan
                                                        ? "border-red-500"
                                                        : "border-slate-300 focus:border-sky-500"
                                                }`}
                                            />
                                            {errors.bolos_alasan && (
                                                <p className="text-xs text-red-500 mt-1">
                                                    {errors.bolos_alasan}
                                                </p>
                                            )}
                                        </div>
                                        <div className="w-20">
                                            <label className="block text-xs font-medium text-neutral-700">
                                                Poin
                                            </label>
                                            <input
                                                type="text"
                                                inputMode="numeric"
                                                id="bolos_poin"
                                                value={formData.bolos_poin}
                                                onChange={(e) =>
                                                    handleFormChange(
                                                        "bolos_poin",
                                                        e.target.value.replace(
                                                            /[^0-9]/g,
                                                            ""
                                                        )
                                                    )
                                                }
                                                placeholder="Poin"
                                                className={`mt-1 w-full px-3 py-2 rounded-xl border focus:outline-none placeholder:text-sm ${
                                                    errors.bolos_poin
                                                        ? "border-red-500"
                                                        : "border-slate-300 focus:border-sky-500"
                                                }`}
                                            />
                                            {errors.bolos_poin && (
                                                <p className="text-xs text-red-500 mt-1">
                                                    {errors.bolos_poin}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                <div className="md:col-span-2">
                                    <div className="flex justify-between items-center mb-2">
                                        <label className="block text-sm font-medium text-neutral-700">
                                            Indisipliner Lainnya
                                        </label>
                                        <ButtonRounded
                                            size="sm"
                                            variant="secondary"
                                            onClick={handleAddOtherIndisipliner}
                                        >
                                            <PlusCircle className="w-4 h-4 mr-2" />
                                            Tambah
                                        </ButtonRounded>
                                    </div>
                                    <div className="space-y-4">
                                        {formData.details.map((item, index) => (
                                            <div
                                                key={index}
                                                className="flex gap-2 items-start"
                                            >
                                                <div className="flex-1 space-y-1">
                                                    <input
                                                        type="text"
                                                        value={
                                                            item.jenis_pelanggaran
                                                        }
                                                        onChange={(e) =>
                                                            handleOtherIndisiplinerChange(
                                                                index,
                                                                "jenis_pelanggaran",
                                                                e.target.value
                                                            )
                                                        }
                                                        placeholder="Jenis pelanggaran..."
                                                        className={`w-full px-3 py-2 rounded-xl border focus:outline-none placeholder:text-sm ${
                                                            errors[
                                                                `details.${index}.jenis_pelanggaran`
                                                            ]
                                                                ? "border-red-500"
                                                                : "border-slate-300 focus:border-sky-500"
                                                        }`}
                                                    />
                                                    {errors[
                                                        `details.${index}.jenis_pelanggaran`
                                                    ] && (
                                                        <p className="text-xs text-red-500 mt-1">
                                                            {
                                                                errors[
                                                                    `details.${index}.jenis_pelanggaran`
                                                                ]
                                                            }
                                                        </p>
                                                    )}
                                                </div>
                                                <div className="w-20 space-y-1">
                                                    <input
                                                        type="text"
                                                        inputMode="numeric"
                                                        value={item.poin}
                                                        onChange={(e) =>
                                                            handleOtherIndisiplinerChange(
                                                                index,
                                                                "poin",
                                                                e.target.value.replace(
                                                                    /[^0-9]/g,
                                                                    ""
                                                                )
                                                            )
                                                        }
                                                        placeholder="Poin"
                                                        className={`w-full px-3 py-2 rounded-xl border focus:outline-none placeholder:text-sm ${
                                                            errors[
                                                                `details.${index}.poin`
                                                            ]
                                                                ? "border-red-500"
                                                                : "border-slate-300 focus:border-sky-500"
                                                        }`}
                                                    />
                                                    {errors[
                                                        `details.${index}.poin`
                                                    ] && (
                                                        <p className="text-xs text-red-500 mt-1">
                                                            {
                                                                errors[
                                                                    `details.${index}.poin`
                                                                ]
                                                            }
                                                        </p>
                                                    )}
                                                </div>
                                                <ButtonRounded
                                                    size="sm"
                                                    variant="icon"
                                                    onClick={(e) =>
                                                        handleRemoveOtherIndisipliner(
                                                            e,
                                                            index
                                                        )
                                                    }
                                                >
                                                    <Trash2 className="w-4 h-4 text-red-500" />
                                                </ButtonRounded>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>
                            <div className="p-4 md:p-6 border-t border-slate-300 flex justify-end">
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
                        </form>
                    </motion.div>
                </motion.div>
            )}
        </AnimatePresence>
    );
};

export default IndisiplinerModal;
