import { Save } from "lucide-react";
import ButtonRounded from "@/Components/common/button-rounded";

const ShowRekapTable = ({
    students,
    isSaving,
    handleInputChange,
    handleSave,
}) => {
    const handleKeyPress = (event) => {
        const charCode = event.which || event.keyCode;
        const currentValue = event.target.value;

        if (charCode >= 48 && charCode <= 57) {
            return true;
        }

        if (charCode === 44 || charCode === 46) {
            if (currentValue.includes(",") || currentValue.includes(".")) {
                event.preventDefault();
            }
            return true;
        }

        if ([8, 9, 13, 37, 39, 46].includes(charCode)) {
            return true;
        }

        event.preventDefault();
    };

    return (
        <div className="overflow-x-auto">
            <table className="w-full text-sm text-left text-neutral-500">
                <thead className="bg-slate-50">
                    <tr>
                        <th className="w-16 px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            No
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Nama
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            NIS
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Poin Tambahan
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Keterangan
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody className="bg-white divide-y divide-slate-200">
                    {students.map((student, index) => (
                        <tr
                            key={student.id}
                            className="even:bg-slate-50 hover:bg-slate-100"
                        >
                            <td className="px-6 py-4 text-sm font-medium whitespace-nowrap text-neutral-800">
                                {index + 1}
                            </td>
                            <td className="py-4 px-6 font-medium text-neutral-800">
                                {student.nama}
                            </td>
                            <td className="py-4 px-6 text-neutral-800">
                                {student.nis}
                            </td>
                            <td className="py-4 px-6">
                                <input
                                    type="text"
                                    inputMode="decimal"
                                    name="poin_tambahan"
                                    value={student.poin_tambahan}
                                    onChange={(e) =>
                                        handleInputChange(e, index)
                                    }
                                    onKeyPress={handleKeyPress}
                                    className="w-20 p-2 text-md border rounded-xl focus:outline-none border-slate-300 focus:border-sky-300 placeholder:text-sm"
                                    placeholder="0"
                                />
                            </td>
                            <td className="py-4 px-6">
                                <input
                                    type="text"
                                    name="keterangan"
                                    value={student.keterangan}
                                    onChange={(e) =>
                                        handleInputChange(e, index)
                                    }
                                    className="w-full p-2 text-md border rounded-xl focus:outline-none border-slate-300 focus:border-sky-300 placeholder:text-sm"
                                    placeholder="Tambahkan keterangan..."
                                />
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-center">
                                <ButtonRounded
                                    onClick={() => handleSave(student)}
                                    disabled={isSaving[student.id]}
                                    variant="outline"
                                    size="sm"
                                >
                                    <Save className="w-4 h-4" />
                                </ButtonRounded>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default ShowRekapTable;
