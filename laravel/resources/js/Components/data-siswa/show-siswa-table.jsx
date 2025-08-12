import { Edit, Trash2, Save, X } from "lucide-react";
import ButtonRounded from "@/Components/common/button-rounded";

const ShowSiswaTable = ({
    students,
    editingId,
    editData,
    editErrors,
    handleEditClick,
    handleUpdate,
    handleDelete,
    handleCancelEdit,
    handleInputChange,
}) => {
    return (
        <div className="overflow-x-auto">
            <table className="w-full">
                <thead className="bg-slate-50">
                    <tr>
                        <th className="w-16 px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            No
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Nama Siswa
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Nomor Induk Siswa
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-center uppercase text-neutral-500">
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
                                {index + 1}.
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap">
                                {editingId === student.id ? (
                                    <div>
                                        <input
                                            type="text"
                                            name="nama"
                                            value={editData.nama}
                                            onChange={handleInputChange}
                                            className={`w-full p-2 text-sm border rounded-xl focus:outline-none ${
                                                editErrors?.nama
                                                    ? "border-red-400"
                                                    : "border-slate-300 focus:border-sky-300"
                                            }`}
                                        />
                                        {editErrors?.nama && (
                                            <p className="mt-1 text-xs text-red-600">
                                                {editErrors.nama[0]}
                                            </p>
                                        )}
                                    </div>
                                ) : (
                                    <div className="text-sm font-medium text-neutral-800">
                                        {student.nama}
                                    </div>
                                )}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap">
                                {editingId === student.id ? (
                                    <div>
                                        <input
                                            type="text"
                                            name="nis"
                                            value={editData.nis}
                                            onChange={(e) => {
                                                if (
                                                    /^\d*$/.test(e.target.value)
                                                ) {
                                                    handleInputChange(e);
                                                }
                                            }}
                                            className={`w-full p-2 text-sm border rounded-xl focus:outline-none ${
                                                editErrors?.nis
                                                    ? "border-red-400"
                                                    : "border-slate-300 focus:border-sky-300"
                                            }`}
                                        />
                                        {editErrors?.nis && (
                                            <p className="mt-1 text-xs text-red-600">
                                                {editErrors.nis[0]}
                                            </p>
                                        )}
                                    </div>
                                ) : (
                                    <div className="text-sm font-medium text-neutral-800">
                                        {student.nis}
                                    </div>
                                )}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-center">
                                {editingId === student.id ? (
                                    <div className="flex items-center justify-center space-x-2">
                                        <ButtonRounded
                                            size="sm"
                                            variant="outline"
                                            onClick={handleCancelEdit}
                                        >
                                            <X size={16} />
                                        </ButtonRounded>
                                        <ButtonRounded
                                            size="sm"
                                            variant="primary"
                                            onClick={(e) =>
                                                handleUpdate(e, student.id)
                                            }
                                        >
                                            <Save size={16} />
                                        </ButtonRounded>
                                    </div>
                                ) : (
                                    <div className="flex items-center justify-center space-x-2">
                                        <ButtonRounded
                                            size="sm"
                                            variant="outline"
                                            onClick={() =>
                                                handleEditClick(student)
                                            }
                                        >
                                            <Edit size={16} />
                                        </ButtonRounded>
                                        <ButtonRounded
                                            size="sm"
                                            variant="primary"
                                            onClick={(e) =>
                                                handleDelete(e, student.id)
                                            }
                                        >
                                            <Trash2 size={16} />
                                        </ButtonRounded>
                                    </div>
                                )}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default ShowSiswaTable;
