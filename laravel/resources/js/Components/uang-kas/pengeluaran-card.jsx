import { FaCheck, FaTimes } from "react-icons/fa";
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

const PengeluaranCard = ({ pengeluarans, role, onApprove, onReject }) => {
    return (
        <div className="grid grid-cols-1 gap-4">
            {pengeluarans.map((item) => (
                <div
                    key={item.id}
                    className="p-4 space-y-3 border rounded-xl border-slate-300"
                >
                    <div className="flex items-start justify-between">
                        <div className="flex flex-col gap-1">
                            <p className="text-sm font-medium text-neutral-800">
                                {item.deskripsi}
                            </p>
                            <p className="text-xs text-neutral-500">
                                {item.tanggal_formatted}
                            </p>
                        </div>
                        <StatusBadge status={item.status} />
                    </div>
                    <div className="pt-2 border-t border-slate-200">
                        <div className="flex items-center justify-between">
                            <p className="text-sm text-neutral-500">Nominal</p>
                            <p className="text-sm font-semibold text-neutral-800">
                                {formatRupiah(item.nominal)}
                            </p>
                        </div>
                    </div>
                    {role === "wali_kelas" && item.status === "pending" && (
                        <div className="flex items-center justify-end space-x-2 pt-2 border-t border-slate-200">
                            <ButtonRounded
                                variant="destructive"
                                size="sm"
                                onClick={() => onReject(item.id)}
                                icon={FaTimes}
                            >
                                Tolak
                            </ButtonRounded>
                            <ButtonRounded
                                variant="success"
                                size="sm"
                                onClick={() => onApprove(item.id)}
                                icon={FaCheck}
                            >
                                Setujui
                            </ButtonRounded>
                        </div>
                    )}
                </div>
            ))}
        </div>
    );
};

export default PengeluaranCard;
