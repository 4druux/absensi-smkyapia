import { Trash2, FileText } from "lucide-react";
import ButtonRounded from "@/Components/common/button-rounded";

const IndisiplinerTable = ({
    indisiplinerData,
    handleDelete,
    handleExportStudent,
}) => {
    const findViolation = (details, type) => {
        return details?.find((detail) => detail.jenis_pelanggaran === type);
    };

    const getOtherViolations = (details) => {
        return details?.filter(
            (detail) =>
                !["Terlambat", "Alfa", "Bolos"].includes(
                    detail.jenis_pelanggaran
                )
        );
    };

    return (
        <div className="overflow-x-auto">
            <table className="w-full table-auto">
                <thead className="bg-slate-50">
                    <tr>
                        <th className="w-12 px-4 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500 align-top">
                            No
                        </th>
                        <th className="w-48 px-4 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500 align-top">
                            Nama Siswa
                        </th>
                        <th className="w-32 px-4 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500 align-top">
                            Nomor Induk Siswa
                        </th>
                        <th className="w-24 px-4 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500 align-top">
                            Jenis Surat
                        </th>
                        <th className="w-24 px-4 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500 align-top">
                            Nomor Surat
                        </th>
                        <th className="px-4 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500 align-top">
                            Keterlambatan
                        </th>
                        <th className="px-4 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500 align-top">
                            Alfa
                        </th>
                        <th className="px-4 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500 align-top">
                            Bolos
                        </th>
                        <th className="px-4 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500 align-top">
                            Indisipliner Lainnya
                        </th>
                        <th className="w-24 px-4 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500 align-top">
                            Tanggal Surat
                        </th>
                        <th className="w-20 px-4 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500 align-top">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody className="bg-white divide-y divide-slate-200">
                    {indisiplinerData && indisiplinerData.length > 0 ? (
                        indisiplinerData.map((data, index) => {
                            const terlambat = findViolation(
                                data.details,
                                "Terlambat"
                            );
                            const alfa = findViolation(data.details, "Alfa");
                            const bolos = findViolation(data.details, "Bolos");
                            const otherViolations = getOtherViolations(
                                data.details
                            );

                            return (
                                <tr
                                    key={data.id}
                                    className="even:bg-slate-50 hover:bg-slate-100"
                                >
                                    <td className="px-4 py-4 text-sm font-medium text-neutral-800 text-center align-top">
                                        {index + 1}
                                    </td>
                                    <td className="px-4 py-4 text-sm text-neutral-700 whitespace-nowrap text-left align-top">
                                        {data.siswa?.nama || "Siswa Dihapus"}
                                    </td>
                                    <td className="px-4 py-4 text-sm text-neutral-700 whitespace-nowrap text-left align-top">
                                        {data.siswa?.nis || "-"}
                                    </td>
                                    <td className="px-4 py-4 text-sm text-neutral-700 whitespace-nowrap text-left align-top">
                                        {data.jenis_surat || "-"}
                                    </td>
                                    <td className="px-4 py-4 text-sm text-neutral-700 whitespace-nowrap text-left align-top">
                                        {data.nomor_surat || "-"}
                                    </td>
                                    <td className="px-4 py-4 text-sm text-neutral-700 break-words max-w-xs text-left align-top">
                                        {terlambat ? (
                                            <>
                                                {terlambat.alasan} (
                                                {terlambat.poin} poin)
                                            </>
                                        ) : (
                                            "-"
                                        )}
                                    </td>
                                    <td className="px-4 py-4 text-sm text-neutral-700 break-words max-w-xs text-left align-top">
                                        {alfa ? (
                                            <>
                                                {alfa.alasan} ({alfa.poin} poin)
                                            </>
                                        ) : (
                                            "-"
                                        )}
                                    </td>
                                    <td className="px-4 py-4 text-sm text-neutral-700 break-words max-w-xs text-left align-top">
                                        {bolos ? (
                                            <>
                                                {bolos.alasan} ({bolos.poin}{" "}
                                                poin)
                                            </>
                                        ) : (
                                            "-"
                                        )}
                                    </td>
                                    <td className="px-4 py-4 text-sm text-neutral-700 break-words max-w-xs text-left align-top">
                                        {otherViolations &&
                                        otherViolations.length > 0 ? (
                                            <ul className="list-disc pl-4">
                                                {otherViolations.map(
                                                    (detail, detailIndex) => (
                                                        <li key={detailIndex}>
                                                            {
                                                                detail.jenis_pelanggaran
                                                            }{" "}
                                                            ({detail.poin} poin)
                                                        </li>
                                                    )
                                                )}
                                            </ul>
                                        ) : (
                                            "-"
                                        )}
                                    </td>
                                    <td className="px-4 py-4 text-sm text-neutral-700 whitespace-nowrap text-left align-top">
                                        {data.tanggal_surat
                                            ? new Date(
                                                  data.tanggal_surat
                                              ).toLocaleDateString("id-ID", {
                                                  day: "2-digit",
                                                  month: "long",
                                                  year: "numeric",
                                              })
                                            : "-"}
                                    </td>
                                    <td className="px-4 py-4 text-sm text-neutral-700 text-left align-top">
                                        <div className="flex gap-2">
                                            <ButtonRounded
                                                size="sm"
                                                variant="outline"
                                                onClick={() =>
                                                    handleDelete(data.id)
                                                }
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
                                    </td>
                                </tr>
                            );
                        })
                    ) : (
                        <tr>
                            <td
                                colSpan="11"
                                className="text-center py-24 text-neutral-500 text-sm"
                            >
                                Tidak ada data indisipliner siswa.
                            </td>
                        </tr>
                    )}
                </tbody>
            </table>
        </div>
    );
};

export default IndisiplinerTable;
