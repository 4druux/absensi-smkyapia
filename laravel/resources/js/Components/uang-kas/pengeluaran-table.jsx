import { formatRupiah } from "@/utils/formatRupiah";
import ButtonRounded from "@/Components/common/button-rounded";

const StatusBadge = ({ status }) => {
    const baseClasses = "px-2 py-1 text-xs font-semibold rounded-full";
    const statusClasses = {
        pending: "bg-yellow-100 text-yellow-800",
        approved: "bg-green-100 text-green-800",
        rejected: "bg-red-100 text-red-800",
    };
    const statusLabels = {
        pending: "Pending",
        approved: "Disetujui",
        rejected: "Ditolak",
    };
    return (
        <span className={`${baseClasses} ${statusClasses[status] || ""}`}>
            {statusLabels[status] || status}
        </span>
    );
};

const PengeluaranTable = ({ pengeluarans, role, onApprove, onReject }) => {
    return (
        <div className="overflow-x-auto">
            <table className="w-full">
                <thead className="bg-slate-50">
                    <tr>
                        <th className="w-16 px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            No
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Tanggal
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Deskripsi
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-right uppercase text-neutral-500">
                            Nominal
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-center uppercase text-neutral-500">
                            Status
                        </th>
                        {(role === "walikelas" || role === "superadmin") && (
                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-center uppercase text-neutral-500">
                                Aksi
                            </th>
                        )}
                    </tr>
                </thead>
                <tbody className="bg-white divide-y divide-slate-200">
                    {pengeluarans.map((item, index) => (
                        <tr
                            key={item.id}
                            className="transition-colors duration-150 hover:bg-slate-50"
                        >
                            <td className="px-6 py-4 text-sm font-medium whitespace-nowrap text-neutral-800">
                                {index + 1}.
                            </td>
                            <td className="px-6 py-4 text-sm whitespace-nowrap text-neutral-600">
                                {item.tanggal_formatted}
                            </td>
                            <td className="px-6 py-4 text-sm whitespace-nowrap text-neutral-600">
                                {item.deskripsi}
                            </td>
                            <td className="px-6 py-4 text-sm font-medium text-right whitespace-nowrap text-neutral-800">
                                {formatRupiah(item.nominal)}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-center">
                                <StatusBadge status={item.status} />
                            </td>
                            {(role === "walikelas" ||
                                role === "superadmin") && (
                                <td className="px-6 py-4 whitespace-nowrap text-center">
                                    {item.status === "pending" ? (
                                        <div className="flex items-center justify-center space-x-2">
                                            <ButtonRounded
                                                variant="outline"
                                                size="sm"
                                                onClick={() =>
                                                    onReject(item.id)
                                                }
                                            >
                                                Tolak
                                            </ButtonRounded>
                                            <ButtonRounded
                                                variant="primary"
                                                size="sm"
                                                onClick={() =>
                                                    onApprove(item.id)
                                                }
                                            >
                                                <span>Setujui</span>
                                            </ButtonRounded>
                                        </div>
                                    ) : (
                                        <span>-</span>
                                    )}
                                </td>
                            )}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default PengeluaranTable;
