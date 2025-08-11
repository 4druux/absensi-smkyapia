import { Trash2 } from "lucide-react";
import Button from "@/Components/common/button";

const ProblemClassCard = ({ problems, handleDelete }) => {
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
                                {new Date(problem.tanggal).toLocaleDateString(
                                    "id-ID",
                                    {
                                        day: "2-digit",
                                        month: "long",
                                        year: "numeric",
                                    }
                                )}
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
                                Pemecahan Masalah:
                            </p>
                            <p className="font-medium text-neutral-800 break-words">
                                {problem.pemecahan}
                            </p>
                        </div>
                    </div>
                ))
            ) : (
                <div className="text-center text-sm py-20 text-neutral-500">
                    Tidak ada data permasalahan kelas.
                </div>
            )}
        </div>
    );
};

export default ProblemClassCard;
