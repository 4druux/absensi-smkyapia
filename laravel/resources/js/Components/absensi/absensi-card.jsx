import { CheckCircle } from "lucide-react";

const AbsensiCard = ({
    students,
    attendance,
    onStatusChange,
    statuses,
    hasAttendanceBeenSaved,
}) => {
    return (
        <div className="grid grid-cols-1 gap-4">
            {students.map((student) => (
                <div
                    key={student.id}
                    className="p-4 bg-white rounded-lg shadow-sm"
                >
                    <div className="flex flex-col items-start justify-between sm:flex-row sm:items-center">
                        <div className="flex flex-col">
                            <span className="text-sm font-medium text-neutral-800">
                                {student.nama}
                            </span>
                            <span className="text-xs text-neutral-500">
                                NIS: {student.nis}
                            </span>
                        </div>
                        <div className="flex flex-wrap justify-end gap-2 mt-2 sm:mt-0">
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
                                        attendance[student.id] === null &&
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
                                            className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium cursor-pointer transition-all duration-200 border ${
                                                isSelected
                                                    ? `${color}`
                                                    : "bg-slate-50 text-slate-500 border-slate-300 hover:bg-slate-200"
                                            }`}
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
                    </div>
                </div>
            ))}
        </div>
    );
};

export default AbsensiCard;
