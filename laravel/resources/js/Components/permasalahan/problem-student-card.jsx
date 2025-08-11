import { Trash2 } from "lucide-react";
import Button from "@/Components/common/button";

const ProblemStudentCard = ({ problems, handleDelete }) => {
    return (
        <div className="grid grid-cols-1 gap-4">
            {problems && problems.length > 0 ? (
                problems.map((problem, index) => (
                    <div
                        key={problem.id}
                        className="p-4 border border-slate-300 rounded-lg space-y-3"
                    >
                        <div className="flex justify-between items-start">
                            <div className="text-sm font-medium text-neutral-800">
                                {problem.siswa?.nama || "Siswa Dihapus"}
                                <span className="block text-xs font-normal text-neutral-500">
                                    {new Date(
                                        problem.tanggal
                                    ).toLocaleDateString("id-ID", {
                                        day: "2-digit",
                                        month: "long",
                                        year: "numeric",
                                    })}
                                </span>
                            </div>
                            <Button
                                size="sm"
                                variant="primary"
                                onClick={() => handleDelete(problem.id)}
                            >
                                <Trash2 className="w-4 h-4" />
                            </Button>
                        </div>
                        <div className="text-xs">
                            <p className="font-medium text-neutral-500">
                                Masalah:
                            </p>
                            <p className="font-medium text-neutral-800 break-words">
                                {problem.masalah}
                            </p>
                        </div>
                        <div className="text-xs">
                            <p className="font-medium text-neutral-500">
                                Tindakan Walas:
                            </p>
                            <p className="font-medium text-neutral-800 break-words">
                                {problem.tindakan_walas}
                            </p>
                        </div>
                    </div>
                ))
            ) : (
                <div className="text-center text-sm py-20 text-neutral-500">
                    Tidak ada data permasalahan siswa.
                </div>
            )}
        </div>
    );
};

export default ProblemStudentCard;
