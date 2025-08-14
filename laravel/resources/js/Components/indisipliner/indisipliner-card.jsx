import { Trash2, FileText } from "lucide-react";
import ButtonRounded from "@/Components/common/button-rounded";

const IndisiplinerCard = ({
    indisiplinerData,
    handleDelete,
    handleExportStudent,
}) => {
    const findViolation = (details, type) => {
        return details?.find((detail) => detail.jenis_pelanggaran === type);
    };

    return (
        <div className="grid grid-cols-1 gap-4">
            {indisiplinerData && indisiplinerData.length > 0 ? (
                indisiplinerData.map((data, index) => {
                    const terlambat = findViolation(data.details, "Terlambat");
                    const alfa = findViolation(data.details, "Alfa");
                    const bolos = findViolation(data.details, "Bolos");

                    const otherViolations = data.details?.filter(
                        (detail) =>
                            !["Terlambat", "Alfa", "Bolos"].includes(
                                detail.jenis_pelanggaran
                            )
                    );

                    return (
                        <div
                            key={data.id}
                            className="p-4 border border-slate-300 rounded-lg space-y-3"
                        >
                            <div className="flex justify-between items-start">
                                <div>
                                    <div className="text-sm font-medium text-neutral-800">
                                       {index + 1}. {data.siswa?.nama || "Siswa Dihapus"}
                                    </div>
                                    <div className="text-xs font-normal text-neutral-500">
                                        {data.siswa?.nis || "-"}
                                    </div>
                                </div>
                                <div className="flex gap-2">
                                    <ButtonRounded
                                        size="sm"
                                        variant="outline"
                                        onClick={() => handleDelete(data.id)}
                                    >
                                        <Trash2 className="w-4 h-4" />
                                    </ButtonRounded>
                                    <ButtonRounded
                                        size="sm"
                                        variant="primary"
                                        onClick={() =>
                                            handleExportStudent(
                                                data.siswa.id,
                                                data.siswa.nama,
                                                index + 1
                                            )
                                        }
                                    >
                                        <FileText className="w-4 h-4" />
                                    </ButtonRounded>
                                </div>
                            </div>

                            <div className="text-xs">
                                <p className="font-medium text-neutral-500">
                                    Tanggal Surat:
                                </p>
                                <p className="font-medium text-neutral-800 break-words">
                                    {data.tanggal_surat
                                        ? new Date(
                                              data.tanggal_surat
                                          ).toLocaleDateString("id-ID", {
                                              day: "2-digit",
                                              month: "long",
                                              year: "numeric",
                                          })
                                        : "-"}
                                </p>
                            </div>

                            <div className="text-xs">
                                <p className="font-medium text-neutral-500">
                                    Jenis Surat:
                                </p>
                                <p className="font-medium text-neutral-800 break-words">
                                    {data.jenis_surat || "-"}
                                </p>
                            </div>

                            <div className="text-xs">
                                <p className="font-medium text-neutral-500">
                                    Nomor Surat:
                                </p>
                                <p className="font-medium text-neutral-800 break-words">
                                    {data.nomor_surat || "-"}
                                </p>
                            </div>

                            {terlambat && (
                                <div className="text-xs">
                                    <p className="font-medium text-neutral-500">
                                        Keterlambatan ({terlambat.poin} poin):
                                    </p>
                                    <p className="font-medium text-neutral-800 break-words">
                                        {terlambat.alasan || "-"}
                                    </p>
                                </div>
                            )}

                            {alfa && (
                                <div className="text-xs">
                                    <p className="font-medium text-neutral-500">
                                        Alfa ({alfa.poin} poin):
                                    </p>
                                    <p className="font-medium text-neutral-800 break-words">
                                        {alfa.alasan || "-"}
                                    </p>
                                </div>
                            )}

                            {bolos && (
                                <div className="text-xs">
                                    <p className="font-medium text-neutral-500">
                                        Bolos ({bolos.poin} poin):
                                    </p>
                                    <p className="font-medium text-neutral-800 break-words">
                                        {bolos.alasan || "-"}
                                    </p>
                                </div>
                            )}

                            {otherViolations && otherViolations.length > 0 && (
                                <div className="text-xs">
                                    <p className="font-medium text-neutral-500">
                                        Indisipliner Lainnya:
                                    </p>
                                    <ul className="list-disc pl-4">
                                        {otherViolations.map(
                                            (detail, detailIndex) => (
                                                <li key={detailIndex}>
                                                    {detail.jenis_pelanggaran} (
                                                    {detail.poin} poin)
                                                </li>
                                            )
                                        )}
                                    </ul>
                                </div>
                            )}
                        </div>
                    );
                })
            ) : (
                <div className="text-center text-sm py-20 text-neutral-500">
                    Tidak ada data indisipliner siswa.
                </div>
            )}
        </div>
    );
};

export default IndisiplinerCard;
