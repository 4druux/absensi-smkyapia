import { FaMoneyBillWave } from "react-icons/fa6";
import { Users, School, BookOpen } from "lucide-react";

const UangKasHeader = ({
    studentData,
    summary,
    nominal,
    onNominalChange,
    isReadOnly,
    formatRupiah,
}) => {
    return (
        <>
            <div className="flex flex-col items-start justify-between gap-4 lg:flex-row lg:items-center">
                <div className="flex items-center space-x-3">
                    <div className="p-3 rounded-lg bg-sky-100">
                        <FaMoneyBillWave className="w-5 h-5 text-sky-600 md:w-6 md:h-6" />
                    </div>
                    <div>
                        <h3 className="text-lg font-medium text-neutral-700">
                            Pembayaran Uang Kas
                        </h3>
                        <div className="flex flex-row gap-2 mt-1 md:items-center">
                            <div className="flex items-center space-x-1 md:space-x-2">
                                <Users className="hidden w-5 h-5 md:block text-neutral-500" />
                                <span className="text-xs font-medium md:text-sm text-neutral-700">
                                    {studentData.students.length} Siswa
                                </span>
                                <span className="block text-neutral-600 md:hidden">
                                    |
                                </span>
                            </div>
                            <div className="flex items-center space-x-1 md:space-x-2">
                                <School className="hidden w-5 h-5 md:block text-neutral-500" />
                                <span className="text-xs font-medium md:text-sm text-neutral-700">
                                    {studentData.classCode}
                                </span>
                                <span className="block text-neutral-600 md:hidden">
                                    |
                                </span>
                            </div>
                            <div className="flex items-center space-x-1 md:space-x-2">
                                <BookOpen className="hidden w-5 h-5 md:block text-neutral-500" />
                                <span className="text-xs font-medium md:text-sm text-neutral-700">
                                    {studentData.major}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6 mt-6">
                <div className="p-3 flex flex-col justify-center text-center gap-1 rounded-lg bg-slate-100/80">
                    <div className="text-sm text-neutral-500">Total Siswa</div>
                    <div className="text-xl text-neutral-800">
                        {summary.totalStudents}
                    </div>
                </div>
                <div className="p-3 flex flex-col justify-center text-center gap-1 rounded-lg bg-slate-100/80">
                    <div className="text-sm text-neutral-500">Sudah Bayar</div>
                    <div className="text-xl text-neutral-800">
                        {summary.paidStudents}
                    </div>
                </div>
                <div className="p-3 flex flex-col justify-center text-center gap-1 rounded-lg bg-slate-100/80">
                    <div className="text-sm text-neutral-500">Belum Bayar</div>
                    <div className="text-xl text-neutral-800">
                        {summary.unpaidStudents}
                    </div>
                </div>
                <div className="p-3 flex flex-col justify-center text-center gap-1 rounded-lg bg-slate-100/80">
                    <div className="text-sm text-neutral-500">Nominal Kas</div>
                    <div className="text-xl text-neutral-800">
                        {isReadOnly ? (
                            <span className="text-sm">
                                {formatRupiah(nominal)}
                            </span>
                        ) : (
                            <input
                                type="number"
                                min="0"
                                value={nominal}
                                onChange={(e) =>
                                    onNominalChange(e.target.value)
                                }
                                placeholder="0"
                                disabled={isReadOnly}
                                className="w-full text-center p-1 bg-white rounded-full focus:outline-none border border-slate-100/80 focus:border-sky-300 disabled:bg-neutral-100 disabled:cursor-not-allowed"
                            />
                        )}
                    </div>
                </div>
                <div className="p-3 flex flex-col justify-center text-center gap-1 rounded-lg bg-slate-100/80">
                    <div className="text-sm text-neutral-500">
                        Total Terkumpul
                    </div>
                    <div className="text-xl text-neutral-800">
                        <span className="text-sm">Rp </span>
                        {summary.totalCollected.toLocaleString("id-ID")}
                    </div>
                </div>
                <div className="p-3 flex flex-col justify-center text-center gap-1 rounded-lg bg-slate-100/80">
                    <div className="text-sm text-neutral-500">Target</div>
                    <div className="text-xl text-neutral-800">
                        <span className="text-sm">Rp </span>
                        {summary.target.toLocaleString("id-ID")}
                    </div>
                </div>
            </div>
        </>
    );
};

export default UangKasHeader;
