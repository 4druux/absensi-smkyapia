import React from "react";
import { CheckCircle } from "lucide-react";

const UangKasCard = ({
    students,
    payments,
    onPaymentChange,
    onSelectAllChange,
    existingPayments,
    allStudentsPaidFromDb,
}) => {
    const allSelected = students.every(
        (student) => payments[student.id]?.status === "paid"
    );

    return (
        <div className="grid grid-cols-1 gap-4">
            <div className="p-4 border rounded-xl border-slate-300">
                <div className="flex items-center justify-between">
                    <p className="text-sm font-medium text-neutral-700">
                        Pilih Semua
                    </p>
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
            </div>

            {students.map((student, index) => (
                <div
                    key={student.id}
                    className="p-4 space-y-3 border rounded-xl border-slate-300"
                >
                    <div className="flex items-center justify-between">
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
                                        <span className="font-normal">
                                            NIS: {""}
                                        </span>
                                        {student.nis}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="flex items-center space-x-2">
                            {existingPayments[student.id]?.status === "paid" ? (
                                <CheckCircle className="w-5 h-5 text-green-600" />
                            ) : (
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
                                        disabled={false}
                                        className="form-checkbox h-4 w-4 text-sky-600 rounded focus:ring-sky-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    />
                                </label>
                            )}
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
};

export default UangKasCard;
