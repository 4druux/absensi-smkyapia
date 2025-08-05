import { useRef, useEffect, useState } from "react";
import { useForm, usePage } from "@inertiajs/react";
import { Save, Users, PlusCircle, Trash2, Upload } from "lucide-react";
import toast from "react-hot-toast";
import Button from "./common/Button";

const StudentDataForm = () => {
    const { props } = usePage();
    const { errors: pageErrors = {} } = props;

    const { data, setData, post, processing, reset } = useForm({
        kelas: "",
        jurusan: "",
        students: [{ id: Date.now(), nis: "", nama: "" }],
    });

    const [clientErrors, setClientErrors] = useState({});
    const fileInputRef = useRef(null);
    const formRef = useRef(null);
    const [importError, setImportError] = useState("");

    useEffect(() => {
        if (props.flash?.success) {
            toast.success(props.flash.success);
        }
        if (props.flash?.error) {
            toast.error(props.flash.error);
        }
    }, [props.flash]);

    const handleStudentChange = (index, field, value) => {
        const updatedStudents = [...data.students];
        updatedStudents[index][field] = value;
        setData("students", updatedStudents);
    };

    const addStudentRow = () => {
        setData("students", [
            ...data.students,
            { id: Date.now(), nis: "", nama: "" },
        ]);
    };

    const removeStudentRow = (id) => {
        if (data.students.length <= 1) {
            toast.error("Minimal harus ada satu baris siswa.");
            return;
        }
        const updatedStudents = data.students.filter(
            (student) => student.id !== id
        );
        setData("students", updatedStudents);
    };

    const handleImportClick = () => {
        setImportError("");
        fileInputRef.current.click();
    };

    const processImportedData = (importedData) => {
        const newStudents = importedData
            .map((row, index) => ({
                id: Date.now() + index,
                nama: (
                    row["NAMA SISWA"] ||
                    row["Nama Siswa"] ||
                    row["nama"] ||
                    row["NAMA"] ||
                    row["Nama"] ||
                    ""
                )
                    .toString()
                    .trim(),
                nis: (
                    row["NOMOR INDUK"] ||
                    row["Nomor Induk"] ||
                    row["nis"] ||
                    row["NIS"] ||
                    ""
                )
                    .toString()
                    .trim(),
            }))
            .filter((s) => s.nis && s.nama);

        if (newStudents.length > 0) {
            toast.success(`${newStudents.length} data siswa berhasil diimpor.`);
            setData("students", newStudents);
        } else {
            setImportError(
                "Gagal memuat data. Pastikan file memiliki kolom 'NAMA SISWA' dan 'NOMOR INDUK'."
            );
        }
    };

    const handleFileChange = (event) => {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        const fileExtension = file.name.split(".").pop().toLowerCase();

        if (fileExtension === "csv") {
            reader.onload = (e) => {
                if (window.Papa) {
                    const { data: jsonData } = window.Papa.parse(
                        e.target.result,
                        { header: true, skipEmptyLines: true }
                    );
                    processImportedData(jsonData);
                } else {
                    setImportError(
                        "Library PapaParse untuk CSV tidak ditemukan."
                    );
                }
            };
            reader.readAsText(file);
        } else if (fileExtension === "xlsx" || fileExtension === "xls") {
            if (typeof window.XLSX === "undefined") {
                setImportError(
                    "Library untuk membaca file Excel (SheetJS) tidak termuat."
                );
                return;
            }
            reader.onload = (e) => {
                try {
                    const workbook = window.XLSX.read(e.target.result, {
                        type: "binary",
                    });
                    const sheetName = workbook.SheetNames[0];
                    const jsonData = window.XLSX.utils.sheet_to_json(
                        workbook.Sheets[sheetName]
                    );
                    processImportedData(jsonData);
                } catch (err) {
                    setImportError(
                        "Gagal memproses file Excel. Pastikan formatnya benar."
                    );
                }
            };
            reader.readAsBinaryString(file);
        } else {
            setImportError(
                "Format file tidak didukung. Gunakan .csv, .xlsx, atau .xls."
            );
        }
        event.target.value = null;
    };

    const validateForm = () => {
        const newErrors = {};
        let firstErrorId = null;

        if (!data.kelas.trim()) {
            newErrors.kelas = "Kode kelas wajib diisi.";
            if (!firstErrorId) firstErrorId = "kelas";
        }

        if (!data.jurusan.trim()) {
            newErrors.jurusan = "Jurusan wajib diisi.";
            if (!firstErrorId) firstErrorId = "jurusan";
        }

        let studentErrorFound = false;
        data.students.forEach((student, index) => {
            if (!student.nama.trim() && !studentErrorFound) {
                if (!firstErrorId) firstErrorId = `student-nama-${index}`;
                studentErrorFound = true;
            }
            if (!student.nis.trim() && !studentErrorFound) {
                if (!firstErrorId) firstErrorId = `student-nis-${index}`;
                studentErrorFound = true;
            }
        });

        const validStudents = data.students.filter(
            (s) => s.nama.trim() && s.nis.trim()
        );
        if (validStudents.length === 0) {
            newErrors.students =
                "Minimal harus ada satu siswa yang valid (nama dan NIS terisi).";
            if (!firstErrorId) firstErrorId = "students-list-error";
        }

        setClientErrors(newErrors);

        return {
            isValid: Object.keys(newErrors).length === 0,
            firstErrorId: firstErrorId,
        };
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        const { isValid, firstErrorId } = validateForm();

        if (!isValid) {
            toast.error(
                "Harap periksa kembali data Anda, ada yang belum terisi."
            );
            if (firstErrorId && formRef.current) {
                const errorElement = formRef.current.querySelector(
                    `#${firstErrorId}`
                );
                if (errorElement) {
                    errorElement.scrollIntoView({
                        behavior: "smooth",
                        block: "center",
                    });
                    errorElement.focus({ preventScroll: true });
                }
            }
            return;
        }

        setClientErrors({});

        const payload = {
            ...data,
            students: data.students.filter((s) => s.nama && s.nis),
        };

        post(route("data-siswa.store"), {
            onSuccess: () => {
                reset();
                setData("students", [{ id: Date.now(), nis: "", nama: "" }]);
            },
            onError: (errors) => {
                toast.error(
                    "Gagal menyimpan data, periksa pesan error di form."
                );

                const firstErrorKey = Object.keys(errors)[0];
                let elementIdToFocus = firstErrorKey;

                if (firstErrorKey.startsWith("students.")) {
                    const [, index, field] = firstErrorKey.split(".");
                    elementIdToFocus = `student-${field}-${index}`;
                }

                const errorElement = formRef.current.querySelector(
                    `#${elementIdToFocus}`
                );
                if (errorElement) {
                    errorElement.scrollIntoView({
                        behavior: "smooth",
                        block: "center",
                    });
                    errorElement.focus({ preventScroll: true });
                }
            },
        });
    };

    const displayErrors = { ...pageErrors, ...clientErrors };

    return (
        <div>
            <div className="px-3 md:px-7 -mt-20 pb-10">
                <div className="bg-white shadow-lg rounded-2xl p-4 md:p-8 flex flex-col space-y-6">
                    <div className="flex items-center space-x-3 mb-8">
                        <div className="p-3 bg-sky-100 rounded-lg">
                            <Users className="w-6 h-6 text-sky-600" />
                        </div>
                        <div>
                            <h3 className="text-md md:text-lg font-medium text-neutral-700">
                                Input Data Kelas & Siswa
                            </h3>
                            <p className="text-xs md:text-sm text-neutral-500">
                                Masukkan informasi kelas dan daftar siswa di
                                bawah ini.
                            </p>
                        </div>
                    </div>

                    <form
                        ref={formRef}
                        onSubmit={handleSubmit}
                        className="space-y-6"
                        noValidate
                    >
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label
                                    htmlFor="kelas"
                                    className="block text-sm font-medium text-neutral-700 mb-2"
                                >
                                    Kode Kelas{" "}
                                    <span className="text-red-600">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="kelas"
                                    value={data.kelas}
                                    onChange={(e) =>
                                        setData("kelas", e.target.value)
                                    }
                                    placeholder="XI-TKJ-1"
                                    className={`w-full px-4 py-3 rounded-xl border transition-colors duration-200 focus:outline-none placeholder:text-sm ${
                                        displayErrors.kelas
                                            ? "border-red-400"
                                            : "border-neutral-300 focus:border-sky-500"
                                    }`}
                                />
                                {displayErrors.kelas && (
                                    <p className="mt-1 text-xs text-red-600">
                                        {displayErrors.kelas}
                                    </p>
                                )}
                            </div>
                            <div>
                                <label
                                    htmlFor="jurusan"
                                    className="block text-sm font-medium text-neutral-700 mb-2"
                                >
                                    Jurusan{" "}
                                    <span className="text-red-600">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="jurusan"
                                    value={data.jurusan}
                                    onChange={(e) =>
                                        setData("jurusan", e.target.value)
                                    }
                                    placeholder="Rekayasa Perangkat Lunak"
                                    className={`w-full px-4 py-3 rounded-xl border transition-colors duration-200 focus:outline-none placeholder:text-sm ${
                                        displayErrors.jurusan
                                            ? "border-red-400"
                                            : "border-neutral-300 focus:border-sky-500"
                                    }`}
                                />
                                {displayErrors.jurusan && (
                                    <p className="mt-1 text-xs text-red-600">
                                        {displayErrors.jurusan}
                                    </p>
                                )}
                            </div>
                        </div>

                        <div className="border-t border-neutral-200 pt-6">
                            <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
                                <label className="block text-sm font-medium text-neutral-700">
                                    Daftar Siswa{" "}
                                    <span className="text-red-600">*</span>
                                </label>
                                <div className="flex items-center justify-end space-x-2">
                                    <input
                                        type="file"
                                        ref={fileInputRef}
                                        onChange={handleFileChange}
                                        style={{ display: "none" }}
                                        accept=".csv, .xlsx, .xls"
                                    />
                                    <button
                                        type="button"
                                        onClick={handleImportClick}
                                        className="flex items-center space-x-2 text-xs font-medium text-sky-600 bg-sky-100 hover:bg-sky-200 p-2 md:px-3 md:py-2 rounded-lg transition-colors cursor-pointer"
                                    >
                                        <Upload size={16} />
                                        <span>Import Data</span>
                                    </button>
                                    <button
                                        type="button"
                                        onClick={addStudentRow}
                                        className="flex items-center space-x-2 text-xs font-medium text-green-600 bg-green-100 hover:bg-green-200 p-2 md:px-3 md:py-2 rounded-lg transition-colors cursor-pointer"
                                    >
                                        <PlusCircle size={16} />
                                        <span>Tambah Siswa</span>
                                    </button>
                                </div>
                            </div>
                            {importError && (
                                <div className="text-center bg-red-50 border border-red-200 text-red-700 text-sm p-3 rounded-lg mb-4 w-fit">
                                    {importError}
                                </div>
                            )}
                            {displayErrors.students && (
                                <div
                                    id="students-list-error"
                                    className="text-center bg-red-50 border border-red-200 text-red-700 text-sm p-3 rounded-lg mb-4 w-fit"
                                >
                                    {displayErrors.students}
                                </div>
                            )}

                            <div className="grid grid-cols-1 gap-4">
                                {data.students.map((student, index) => (
                                    <div
                                        key={student.id}
                                        className="flex flex-col md:flex-row items-end md:items-center gap-3 border border-neutral-300 md:border-none p-4 md:p-0 rounded-xl"
                                    >
                                        <div className="flex items-start md:items-center justify-between gap-2 w-full">
                                            <div className="text-sm text-neutral-600 font-medium pr-2 mt-0 md:mt-6">
                                                {index + 1}.
                                            </div>
                                            <div className="flex flex-col md:flex-row items-center justify-between gap-4 w-full">
                                                <div className="w-full">
                                                    <label
                                                        htmlFor={`student-nama-${index}`}
                                                        className="block text-sm font-medium text-neutral-700 mb-2"
                                                    >
                                                        Nama Siswa{" "}
                                                        <span className="text-red-600">
                                                            *
                                                        </span>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        id={`student-nama-${index}`}
                                                        value={student.nama}
                                                        onChange={(e) =>
                                                            handleStudentChange(
                                                                index,
                                                                "nama",
                                                                e.target.value
                                                            )
                                                        }
                                                        placeholder="Nama Lengkap Siswa"
                                                        className={`w-full px-4 py-2.5 rounded-xl border focus:outline-none placeholder:text-sm ${
                                                            displayErrors[
                                                                `students.${index}.nama`
                                                            ]
                                                                ? "border-red-500"
                                                                : "border-neutral-300 focus:border-sky-500"
                                                        }`}
                                                    />
                                                    {displayErrors[
                                                        `students.${index}.nama`
                                                    ] && (
                                                        <p className="mt-1 text-xs text-red-600">
                                                            {
                                                                displayErrors[
                                                                    `students.${index}.nama`
                                                                ]
                                                            }
                                                        </p>
                                                    )}
                                                </div>
                                                <div className="w-full">
                                                    <label
                                                        htmlFor={`student-nis-${index}`}
                                                        className="block text-sm font-medium text-neutral-700 mb-2"
                                                    >
                                                        Nomor Induk{" "}
                                                        <span className="text-red-600">
                                                            *
                                                        </span>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        id={`student-nis-${index}`}
                                                        value={student.nis}
                                                        onChange={(e) =>
                                                            handleStudentChange(
                                                                index,
                                                                "nis",
                                                                e.target.value
                                                            )
                                                        }
                                                        placeholder="Nomor Induk Siswa"
                                                        className={`w-full px-4 py-2.5 rounded-xl border focus:outline-none placeholder:text-sm ${
                                                            displayErrors[
                                                                `students.${index}.nis`
                                                            ]
                                                                ? "border-red-500"
                                                                : "border-neutral-300 focus:border-sky-500"
                                                        }`}
                                                    />
                                                    {displayErrors[
                                                        `students.${index}.nis`
                                                    ] && (
                                                        <p className="mt-1 text-xs text-red-600">
                                                            {
                                                                displayErrors[
                                                                    `students.${index}.nis`
                                                                ]
                                                            }
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                        <div className="w-full md:w-auto mt-2 md:mt-6 flex justify-end">
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    removeStudentRow(student.id)
                                                }
                                                className="p-2 text-red-500 hover:bg-red-100 rounded-full cursor-pointer"
                                            >
                                                <Trash2 size={18} />
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="pt-6 flex justify-end border-t border-neutral-200">
                            <Button
                                type="submit"
                                variant="primary"
                                disabled={processing}
                            >
                                <Save className="w-4 h-4 mr-2" />
                                {processing ? "Menyimpan..." : "Simpan"}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
};

export default StudentDataForm;
