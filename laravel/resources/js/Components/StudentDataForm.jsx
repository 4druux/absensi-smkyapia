import React, { useState, useRef } from "react";
import { Save, Users, PlusCircle, Trash2, Upload } from "lucide-react";
import Papa from "papaparse";

const StudentDataForm = ({ onSaveAndContinue, initialData }) => {
    // Component states
    const [classCode, setClassCode] = useState(initialData?.classCode || "");
    const [major, setMajor] = useState(initialData?.major || "");
    const [students, setStudents] = useState(
        initialData?.students || [{ id: Date.now(), studentId: "", name: "" }]
    );
    const [errors, setErrors] = useState({});
    const [importError, setImportError] = useState(null);
    const fileInputRef = useRef(null);

    // Handler for student input changes
    const handleStudentChange = (index, field, value) => {
        const updatedStudents = [...students];
        updatedStudents[index][field] = value;
        setStudents(updatedStudents);
    };

    // Handler to add a new empty student row
    const addStudentRow = () => {
        setStudents([...students, { id: Date.now(), studentId: "", name: "" }]);
    };

    // Handler to remove a student row
    const removeStudentRow = (id) => {
        const updatedStudents = students.filter((student) => student.id !== id);
        setStudents(updatedStudents);
    };

    // Triggers the hidden file input
    const handleImportClick = () => {
        fileInputRef.current.click();
    };

    // Centralized function to process the parsed data
    const processImportedData = (data) => {
        if (!data || data.length === 0) {
            setImportError(
                "Tidak ada data yang dapat diimpor dari file. Pastikan file tidak kosong."
            );
            return;
        }

        const importedStudents = data
            .map((row, index) => ({
                id: Date.now() + index,
                name:
                    row["NAMA SISWA"]?.trim() ||
                    row["Nama Siswa"]?.trim() ||
                    row["nama siswa"]?.trim() ||
                    row["name"]?.trim() ||
                    "",
                studentId: String(
                    row["NOMOR INDUK"] ||
                        row["Nomor Induk"] ||
                        row["nomor induk"] ||
                        row["studentId"] ||
                        ""
                ).trim(),
            }))
            .filter((s) => s.studentId && s.name);

        if (importedStudents.length > 0) {
            setStudents(importedStudents);
        } else {
            const headers = Object.keys(data[0] || {}).join(", ");
            console.error("Header yang terdeteksi:", headers);
            setImportError(
                `Gagal menemukan kolom yang cocok. Pastikan file Anda memiliki header 'NAMA SISWA' dan 'NOMOR INDUK'. Header yang terdeteksi: ${headers}`
            );
        }
    };

    const handleFileChange = (event) => {
        const file = event.target.files[0];
        if (!file) return;
        setImportError(null);

        const fileExtension = file.name.split(".").pop().toLowerCase();
        const fileMimeType = file.type;

        console.log(
            `File detected: Name=${file.name}, MIME Type=${fileMimeType}, Extension=${fileExtension}`
        );

        if (fileMimeType === "text/csv" || fileExtension === "csv") {
            Papa.parse(file, {
                header: true,
                skipEmptyLines: true,
                complete: (results) => {
                    processImportedData(results.data);
                },
                error: (error) => {
                    setImportError(
                        "Gagal memproses file CSV. Periksa format file Anda."
                    );
                    console.error("Error parsing CSV:", error);
                },
            });
        } else if (
            fileMimeType ===
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" ||
            fileMimeType === "application/vnd.ms-excel" ||
            fileExtension === "xlsx" ||
            fileExtension === "xls"
        ) {
            if (typeof window.XLSX === "undefined") {
                setImportError(
                    "Library untuk membaca file Excel (SheetJS) tidak termuat. Pastikan Anda telah menambahkan tag <script> di file HTML utama Anda."
                );
                console.error(
                    "SheetJS (XLSX) library not found on window object."
                );
                event.target.value = null; // Reset input
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const XLSX = window.XLSX;
                    const data = e.target.result;
                    const workbook = XLSX.read(new Uint8Array(data), {
                        type: "array",
                    });
                    const sheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[sheetName];
                    const json = XLSX.utils.sheet_to_json(worksheet);
                    processImportedData(json);
                } catch (err) {
                    setImportError(
                        "Gagal memproses file Excel. File mungkin rusak atau formatnya tidak didukung."
                    );
                    console.error("Error processing Excel file:", err);
                }
            };
            reader.onerror = (err) => {
                setImportError("Gagal membaca file.");
                console.error("FileReader error:", err);
            };
            reader.readAsArrayBuffer(file);
        } else {
            setImportError(
                `Format file tidak didukung. Silakan gunakan file .csv atau .xlsx.`
            );
        }

        event.target.value = null;
    };

    const validateForm = () => {
        const newErrors = {};
        if (!classCode.trim()) newErrors.classCode = "Kode kelas harus diisi";
        if (!major.trim()) newErrors.major = "Jurusan harus diisi";

        const studentErrors = [];
        let hasStudentError = false;
        if (
            students.length === 0 ||
            students.every((s) => !s.name && !s.studentId)
        ) {
            newErrors.students = "Minimal harus ada satu siswa.";
            hasStudentError = true;
        } else {
            students.forEach((student, index) => {
                const error = {};
                if (!String(student.studentId).trim()) {
                    error.studentId = "Nomor induk harus diisi";
                    hasStudentError = true;
                }
                if (!String(student.name).trim()) {
                    error.name = "Nama siswa harus diisi";
                    hasStudentError = true;
                }
                studentErrors[index] = error;
            });
        }
        if (hasStudentError) newErrors.studentErrors = studentErrors;

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    // Form submission handler
    const handleSubmit = (e) => {
        e.preventDefault();
        if (validateForm()) {
            const finalStudentData = {
                classCode: classCode.trim(),
                major: major.trim(),
                students: students
                    .map((s) => ({
                        studentId: String(s.studentId).trim(),
                        name: String(s.name).trim(),
                    }))
                    .filter((s) => s.studentId && s.name),
            };
            onSaveAndContinue(finalStudentData);
        }
    };

    return (
        <div>
            <div className="px-3 md:px-7 h-[120px] bg-sky-500 rounded-b-4xl shadow-lg">
                <h3 className="text-white">Data Siswa</h3>
            </div>

            {/* Form Content */}
            <div className="px-3 md:px-7 -mt-20">
                <div className="bg-white rounded-2xl p-6 md:p-8 shadow-lg">
                    <div className="flex items-center space-x-3 mb-8">
                        <div className="p-3 bg-sky-100 rounded-lg">
                            <Users className="w-6 h-6 text-sky-600" />
                        </div>
                        <div>
                            <h3 className="text-md md:text-lg font-medium text-gray-700">
                                Input Data Kelas & Siswa
                            </h3>
                            <p className="text-xs md:text-sm text-gray-500">
                                Masukkan informasi kelas dan daftar siswa di
                                bawah ini.
                            </p>
                        </div>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Class Info */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label
                                    htmlFor="classCode"
                                    className="block text-sm font-medium text-neutral-700 mb-2"
                                >
                                    Kode Kelas{" "}
                                    <span className="text-red-600">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="classCode"
                                    value={classCode}
                                    onChange={(e) =>
                                        setClassCode(e.target.value)
                                    }
                                    placeholder="Contoh: XI-RPL-1"
                                    className={`w-full px-4 py-3 rounded-xl border transition-colors duration-200 focus:outline-none placeholder:text-sm ${
                                        errors.classCode
                                            ? "border-red-400"
                                            : "border-neutral-300 focus:border-sky-500"
                                    }`}
                                />
                                {errors.classCode && (
                                    <p className="mt-1 text-xs text-red-600">
                                        {errors.classCode}
                                    </p>
                                )}
                            </div>
                            <div>
                                <label
                                    htmlFor="major"
                                    className="block text-sm font-medium text-neutral-700 mb-2"
                                >
                                    Jurusan{" "}
                                    <span className="text-red-600">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="major"
                                    value={major}
                                    onChange={(e) => setMajor(e.target.value)}
                                    placeholder="Contoh: Rekayasa Perangkat Lunak"
                                    className={`w-full px-4 py-3 rounded-xl border transition-colors duration-200 focus:outline-none placeholder:text-sm ${
                                        errors.major
                                            ? "border-red-400"
                                            : "border-neutral-300 focus:border-sky-500"
                                    }`}
                                />
                                {errors.major && (
                                    <p className="mt-1 text-xs text-red-600">
                                        {errors.major}
                                    </p>
                                )}
                            </div>
                        </div>

                        {/* Student List */}
                        <div className="border-t border-gray-200 pt-6">
                            <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
                                <label
                                    htmlFor="studentFile"
                                    className="block text-sm font-medium text-neutral-700 mb-2"
                                >
                                    Daftar Siswa{" "}
                                    <span className="text-red-600">*</span>
                                </label>

                                <div className="flex items-center justify-end space-x-2">
                                    <input
                                        type="file"
                                        ref={fileInputRef}
                                        onChange={handleFileChange}
                                        style={{ display: "none" }}
                                        accept=".csv, .xlsx, .xls, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
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
                                <div className="bg-red-50 border border-red-200 text-red-700 text-sm p-3 rounded-lg mb-4">
                                    {importError}
                                </div>
                            )}

                            <div className="grid grid-cols-1 gap-4">
                                {students.map((student, index) => (
                                    <div
                                        key={student.id}
                                        className="flex flex-col md:flex-row items-end md:items-center gap-3 border border-gray-300 md:border-none p-4 md:p-0 rounded-xl "
                                    >
                                        <div className="flex items-start md:items-center justify-between gap-2 w-full">
                                            <div className="text-sm text-neutral-600 font-medium pr-2 mt-0 md:mt-6">
                                                {index + 1}.
                                            </div>

                                            <div className="flex flex-col md:flex-row items-center justify-between gap-4 w-full">
                                                <div className="w-full">
                                                    <label
                                                        htmlFor={`studentName-${index}`}
                                                        className="block text-sm font-medium text-neutral-700 mb-2"
                                                    >
                                                        Nama Siswa{" "}
                                                        <span className="text-red-600">
                                                            *
                                                        </span>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        value={student.name}
                                                        onChange={(e) =>
                                                            handleStudentChange(
                                                                index,
                                                                "name",
                                                                e.target.value
                                                            )
                                                        }
                                                        placeholder="Nama Lengkap Siswa"
                                                        className={`w-full px-4 py-2.5 rounded-xl border focus:outline-none placeholder:text-sm ${
                                                            errors
                                                                .studentErrors?.[
                                                                index
                                                            ]?.name
                                                                ? "border-red-600"
                                                                : "border-gray-300"
                                                        }`}
                                                    />
                                                </div>

                                                <div className="w-full">
                                                    <label
                                                        htmlFor={`studentId-${index}`}
                                                        className="block text-sm font-medium text-neutral-700 mb-2"
                                                    >
                                                        Nomor Induk{" "}
                                                        <span className="text-red-600">
                                                            *
                                                        </span>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        value={
                                                            student.studentId
                                                        }
                                                        onChange={(e) =>
                                                            handleStudentChange(
                                                                index,
                                                                "studentId",
                                                                e.target.value
                                                            )
                                                        }
                                                        placeholder="Nomor Induk"
                                                        className={`w-full px-4 py-2.5 rounded-xl border focus:outline-none placeholder:text-sm ${
                                                            errors
                                                                .studentErrors?.[
                                                                index
                                                            ]?.studentId
                                                                ? "border-red-600"
                                                                : "border-gray-300"
                                                        }`}
                                                    />
                                                </div>
                                            </div>
                                        </div>

                                        <div className="mt-0 md:mt-8">
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    removeStudentRow(student.id)
                                                }
                                                className="p-2 text-red-500 hover:bg-red-100 rounded-full transition-colors cursor-pointer"
                                            >
                                                <Trash2 size={18} />
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                            {errors.students && (
                                <p className="mt-2 text-sm text-red-600">
                                    {errors.students}
                                </p>
                            )}
                        </div>

                        {/* Submit Button */}
                        <div className="pt-6 flex justify-end border-t border-gray-200">
                            <button
                                type="submit"
                                className="flex items-center justify-center bg-sky-600 hover:bg-sky-700 text-white font-semibold py-3 px-6 rounded-full transition-colors duration-200 space-x-2 shadow-md hover:shadow-lg text-sm"
                            >
                                <Save className="w-4 h-4" />
                                <span>Simpan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
};

export default StudentDataForm;
