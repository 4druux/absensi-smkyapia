import { CheckCircle } from "lucide-react";

const AbsensiCard = ({ students, attendance, onStatusChange, statuses }) => {
    return (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            {students.map((student, index) => (
                <div
                    key={student.id}
                    className="p-4 space-y-3 border rounded-xl border-neutral-300"
                >
                    <div className="flex items-start justify-between">
                        <div className="flex items-start gap-2">
                            <p className="text-sm font-medium text-neutral-800">
                                {index + 1}.
                            </p>
                            <div className="flex flex-col gap-1">
                                <p className="text-sm font-medium text-neutral-800">
                                    {student.nama}
                                </p>
                                <p className="text-sm font-medium text-neutral-800">
                                    {student.nis}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="pt-3 border-t border-neutral-300">
                        <p className="mb-2 text-xs font-medium text-neutral-600">
                            Status Kehadiran:
                        </p>
                        <div className="flex flex-wrap gap-2">
                            {statuses.map(({ key, label, color }) => (
                                <label
                                    key={key}
                                    className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium cursor-pointer transition-all duration-200 border ${
                                        attendance[student.id] === key
                                            ? `${color}`
                                            : "bg-neutral-100 text-neutral-500 border-neutral-300 hover:bg-neutral-200"
                                    }`}
                                >
                                    <input
                                        type="checkbox"
                                        checked={attendance[student.id] === key}
                                        onChange={() =>
                                            onStatusChange(student.id, key)
                                        }
                                        className="sr-only"
                                    />
                                    {attendance[student.id] === key && (
                                        <CheckCircle className="w-3 h-3 mr-1.5" />
                                    )}
                                    {label}
                                </label>
                            ))}
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
};

export default AbsensiCard;
