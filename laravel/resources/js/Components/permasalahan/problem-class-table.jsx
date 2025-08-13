import { Trash2 } from "lucide-react";
import ButtonRounded from "@/Components/common/button-rounded";

const ProblemClassTable = ({ problems, handleDelete }) => {
    return (
        <div className="overflow-x-auto">
            <table className="w-full table-auto">
                <thead className="bg-slate-50">
                    <tr>
                        <th className="w-16 px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500 align-top">
                            No
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500 align-top">
                            Tgl/Bln/Tahun
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500 align-top">
                            Masalah Kelas
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500 align-top">
                            Pemecahan Masalah
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500 align-top">
                            Keterangan
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500 align-top">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody className="bg-white divide-y divide-slate-200">
                    {problems && problems.length > 0 ? (
                        problems.map((problem, index) => (
                            <tr
                                key={problem.id}
                                className="even:bg-slate-50 hover:bg-slate-100"
                            >
                                <td className="px-6 py-4 text-sm font-medium text-neutral-800 text-left align-top">
                                    {index + 1}
                                </td>
                                <td className="px-6 py-4 text-sm text-neutral-700 whitespace-nowrap text-left align-top">
                                    {new Date(
                                        problem.tanggal
                                    ).toLocaleDateString("id-ID", {
                                        day: "2-digit",
                                        month: "long",
                                        year: "numeric",
                                    })}
                                </td>
                                <td className="px-6 py-4 text-sm text-neutral-700 break-words max-w-xs text-left align-top">
                                    {problem.masalah}
                                </td>
                                <td className="px-6 py-4 text-sm text-neutral-700 break-words max-w-xs text-left align-top">
                                    {problem.pemecahan}
                                </td>
                                <td className="px-6 py-4 text-sm text-neutral-700 break-words max-w-xs text-left align-top">
                                    {problem.keterangan}
                                </td>
                                <td className="px-6 py-4 text-left align-top">
                                    <ButtonRounded
                                        size="sm"
                                        variant="primary"
                                        onClick={() => handleDelete(problem.id)}
                                    >
                                        <Trash2 className="w-4 h-4" />
                                    </ButtonRounded>
                                </td>
                            </tr>
                        ))
                    ) : (
                        <tr>
                            <td
                                colSpan="7"
                                className="text-center py-24 text-neutral-500"
                            >
                                Tidak ada data permasalahan kelas.
                            </td>
                        </tr>
                    )}
                </tbody>
            </table>
        </div>
    );
};

export default ProblemClassTable;
