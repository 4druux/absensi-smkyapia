import { ClipboardCheck, Users, School, BookOpen } from "lucide-react";

const AbsensiHeader = ({ studentData, tanggalAbsen, summary }) => {
    return (
        <>
            <div className="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                <div className="flex items-center space-x-3">
                    <div className="p-3 rounded-lg bg-sky-100">
                        <ClipboardCheck className="w-5 h-5 text-sky-600 md:w-6 md:h-6" />
                    </div>
                    <div>
                        <h3 className="text-lg font-medium text-neutral-700">
                            Absensi Siswa
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

                {tanggalAbsen && (
                    <p className="mt-1 text-xs font-semibold md:text-sm text-sky-600">
                        Tercatat pada: {tanggalAbsen}
                    </p>
                )}
            </div>

            <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <div className="p-3 text-center rounded-lg bg-neutral-100/80">
                    <div className="text-sm text-neutral-500">Hadir</div>
                    <div className="text-xl text-neutral-800">
                        {summary.present}
                    </div>
                </div>
                <div className="p-3 text-center rounded-lg bg-neutral-100/80">
                    <div className="text-sm text-neutral-500">Telat</div>
                    <div className="text-xl text-neutral-800">
                        {summary.telat}
                    </div>
                </div>
                <div className="p-3 text-center rounded-lg bg-neutral-100/80">
                    <div className="text-sm text-neutral-500">Sakit</div>
                    <div className="text-xl text-neutral-800">
                        {summary.sakit}
                    </div>
                </div>
                <div className="p-3 text-center rounded-lg bg-neutral-100/80">
                    <div className="text-sm text-neutral-500">Izin</div>
                    <div className="text-xl text-neutral-800">
                        {summary.izin}
                    </div>
                </div>
                <div className="p-3 text-center rounded-lg bg-neutral-100/80">
                    <div className="text-sm text-neutral-500">Alfa</div>
                    <div className="text-xl text-neutral-800">
                        {summary.alfa}
                    </div>
                </div>
                <div className="p-3 text-center rounded-lg bg-neutral-100/80">
                    <div className="text-sm text-neutral-500">Bolos</div>
                    <div className="text-xl text-neutral-800">
                        {summary.bolos}
                    </div>
                </div>
            </div>
        </>
    );
};

export default AbsensiHeader;
