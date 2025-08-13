const UangKasTable = ({
    students,
    payments,
    onPaymentChange,
    onSelectAllChange,
    allStudentsPaidFromDb,
}) => {
    const allSelected = students.every(
        (student) => payments[student.id]?.status === "paid"
    );

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
                            <div className="flex items-center justify-center space-x-2">
                                <span>Status Bayar</span>
                                <label className="inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={allSelected}
                                        onChange={(e) =>
                                            onSelectAllChange(e.target.checked)
                                        }
                                        disabled={allStudentsPaidFromDb}
                                        className="form-checkbox h-4 w-4 text-sky-600 rounded focus:ring-sky-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    />
                                </label>
                            </div>
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
                            <td className="px-6 py-4 whitespace-nowrap text-center">
                                <div className="flex items-center justify-center">
                                    <label className="inline-flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            checked={
                                                payments[student.id]?.status ===
                                                "paid"
                                            }
                                            onChange={(e) =>
                                                onPaymentChange(
                                                    student.id,
                                                    "status",
                                                    e.target.checked
                                                        ? "paid"
                                                        : "unpaid"
                                                )
                                            }
                                            className="form-checkbox h-5 w-5 text-sky-600 rounded focus:ring-sky-500"
                                        />
                                    </label>
                                </div>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default UangKasTable;
