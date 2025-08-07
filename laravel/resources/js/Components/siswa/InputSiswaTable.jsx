import React from "react";
import { Trash2 } from "lucide-react";
import Button from "@/Components/common/Button";

const InputSiswaTable = ({
    students,
    handleStudentChange,
    removeStudentRow,
    displayErrors,
}) => {
    return (
        <div className="overflow-x-auto">
            <table className="w-full">
                <thead className="bg-slate-50">
                    <tr>
                        <th className="w-16 px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            No
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Nama Siswa <span className="text-red-600">*</span>
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Nomor Induk <span className="text-red-600">*</span>
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-center uppercase text-neutral-500">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody className="bg-white divide-y divide-slate-200">
                    {students.map((student, index) => (
                        <tr
                            key={student.id}
                            className="even:bg-slate-50 hover:bg-slate-100"
                        >
                            <td className="px-6 py-4 text-sm font-medium whitespace-nowrap text-neutral-800">
                                {index + 1}.
                            </td>
                            <td className="px-6 py-4">
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
                                        {
                                            displayErrors[
                                                `students.${index}.nama`
                                            ]
                                        }
                                    </p>
                                )}
                            </td>
                            <td className="px-6 py-4">
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
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-center">
                                <Button
                                    size="sm"
                                    variant="primary"
                                    onClick={() => removeStudentRow(student.id)}
                                >
                                    <Trash2 size={16} />
                                </Button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default InputSiswaTable;
