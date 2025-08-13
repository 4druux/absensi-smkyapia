import { CheckCircle } from "lucide-react";

const AbsensiTable = ({
    students,
    attendance,
    onStatusChange,
    statuses,
    hasAttendanceBeenSaved,
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
                            Nama Siswa
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Nomor Induk Siswa
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-center uppercase text-neutral-500">
                            Status Kehadiran
                        </th>
                    </tr>
                </thead>
                <tbody className="bg-white divide-y divide-slate-200">
                    {students.map((student, index) => (
                        <tr
                            key={student.id}
                            className="transition-colors duration-150 even:bg-slate-50 hover:bg-slate-100"
                        >
                            <td className="px-6 py-4 text-sm font-medium whitespace-nowrap text-neutral-800">
                                {index + 1}.
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap">
                                <div className="text-sm font-medium text-neutral-800">
                                    {student.nama}
                                </div>
                            </td>
                            <td className="px-12 py-4 whitespace-nowrap">
                                <div className="text-sm font-medium text-neutral-800">
                                    {student.nis}
                                </div>
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap">
                                <div className="flex flex-wrap justify-center gap-2">
                                    {statuses.map(({ key, label, color }) => {
                                        if (
                                            key === "hadir" &&
                                            !hasAttendanceBeenSaved &&
                                            attendance[student.id] === null
                                        ) {
                                            return null;
                                        }

                                        const isSelected =
                                            attendance[student.id] === key ||
                                            (!hasAttendanceBeenSaved &&
                                                attendance[student.id] ===
                                                    null &&
                                                key === "hadir");

                                        const displayLabel =
                                            key === "hadir" &&
                                            attendance[student.id] === null &&
                                            !hasAttendanceBeenSaved ? (
                                                <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border bg-green-100 text-green-800 border-green-300">
                                                    <CheckCircle className="w-3 h-3 mr-1.5" />{" "}
                                                    Hadir
                                                </span>
                                            ) : (
                                                <label
                                                    className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium cursor-pointer transition-all duration-200 border
                                                        ${
                                                            isSelected
                                                                ? `${color}`
                                                                : "bg-slate-50 text-slate-500 border-slate-300 hover:bg-slate-200"
                                                        }
                                                    `}
                                                >
                                                    <input
                                                        type="checkbox"
                                                        checked={isSelected}
                                                        onChange={() =>
                                                            onStatusChange(
                                                                student.id,
                                                                key
                                                            )
                                                        }
                                                        className="sr-only"
                                                    />
                                                    {isSelected && (
                                                        <CheckCircle className="w-3 h-3 mr-1.5" />
                                                    )}
                                                    {label}
                                                </label>
                                            );

                                        return displayLabel;
                                    })}
                                </div>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default AbsensiTable;
