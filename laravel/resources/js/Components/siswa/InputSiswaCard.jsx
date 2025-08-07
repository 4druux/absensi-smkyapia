import { Trash2 } from "lucide-react";
import Button from "@/Components/common/Button";

const InputSiswaCard = ({
    students,
    handleStudentChange,
    removeStudentRow,
    displayErrors,
}) => {
    return (
        <div className="grid grid-cols-1 gap-4">
            {students.map((student, index) => (
                <div
                    key={student.id}
                    className="flex flex-col gap-3 p-4 border rounded-xl border-slate-300"
                >
                    <div className="flex items-center justify-between">
                        <p className="text-sm font-medium text-neutral-800">
                            Siswa {index + 1}
                        </p>
                        <Button
                            size="sm"
                            variant="primary"
                            onClick={() => removeStudentRow(student.id)}
                        >
                            <Trash2 size={16} />
                        </Button>
                    </div>

                    <div className="flex flex-col gap-4">
                        <div>
                            <label
                                htmlFor={`student-nama-${index}`}
                                className="block text-sm font-medium text-neutral-700 mb-2"
                            >
                                Nama Siswa{" "}
                                <span className="text-red-600">*</span>
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
                                    displayErrors[`students.${index}.nama`]
                                        ? "border-red-500"
                                        : "border-slate-300 focus:border-sky-500"
                                }`}
                            />
                            {displayErrors[`students.${index}.nama`] && (
                                <p className="mt-1 text-xs text-red-600">
                                    {displayErrors[`students.${index}.nama`]}
                                </p>
                            )}
                        </div>
                        <div>
                            <label
                                htmlFor={`student-nis-${index}`}
                                className="block text-sm font-medium text-neutral-700 mb-2"
                            >
                                Nomor Induk{" "}
                                <span className="text-red-600">*</span>
                            </label>
                            <input
                                type="text"
                                id={`student-nis-${index}`}
                                value={student.nis}
                                onChange={(e) => {
                                    const value = e.target.value;
                                    if (/^\d*$/.test(value)) {
                                        handleStudentChange(
                                            index,
                                            "nis",
                                            value
                                        );
                                    }
                                }}
                                placeholder="Nomor Induk Siswa"
                                className={`w-full px-4 py-2.5 rounded-xl border focus:outline-none placeholder:text-sm ${
                                    displayErrors[`students.${index}.nis`]
                                        ? "border-red-500"
                                        : "border-slate-300 focus:border-sky-500"
                                }`}
                            />
                            {displayErrors[`students.${index}.nis`] && (
                                <p className="mt-1 text-xs text-red-600">
                                    {displayErrors[`students.${index}.nis`]}
                                </p>
                            )}
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
};

export default InputSiswaCard;
