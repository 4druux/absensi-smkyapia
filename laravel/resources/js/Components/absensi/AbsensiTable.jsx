import { CheckCircle } from "lucide-react";

const AbsensiTable = ({ students, attendance, onStatusChange, statuses }) => {
    return (
        <div className="overflow-x-auto">
            <table className="w-full">
                <thead className="bg-neutral-50">
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
                <tbody className="bg-white divide-y divide-neutral-200">
                    {students.map((student, index) => (
                        <tr
                            key={student.id}
                            className="transition-colors duration-150 even:bg-neutral-50 hover:bg-neutral-100"
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
                                                checked={
                                                    attendance[student.id] ===
                                                    key
                                                }
                                                onChange={() =>
                                                    onStatusChange(
                                                        student.id,
                                                        key
                                                    )
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
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default AbsensiTable;
