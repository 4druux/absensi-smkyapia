import { CheckCircle } from "lucide-react";

const BerandaAbsensiCard = ({ students, attendance, statuses }) => {
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
                        <div className="flex flex-wrap justify-start gap-2 mt-2 sm:mt-0">
                            {attendance[student.id] ? (
                                statuses.map(({ key, label, color }) => {
                                    if (attendance[student.id] === key) {
                                        return (
                                            <span
                                                key={key}
                                                className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border ${color}`}
                                            >
                                                <CheckCircle className="w-3 h-3 mr-1.5" />
                                                {label}
                                            </span>
                                        );
                                    }
                                    return null;
                                })
                            ) : (
                                <span className="text-sm text-neutral-500">
                                    -
                                </span>
                            )}
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
};

export default BerandaAbsensiCard;
