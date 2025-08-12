import { Edit, Trash2, Save, X } from "lucide-react";
import ButtonRounded from "@/Components/common/button-rounded";

const ShowSiswaCard = ({
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
        <div className="grid grid-cols-1 gap-4">
            {students.map((student, index) => (
                <div
                    key={student.id}
                    className="p-4 space-y-3 border rounded-xl border-slate-300"
                >
                    <div className="flex items-start justify-between">
                        <div className="flex items-start gap-2">
                            <p className="text-sm font-medium text-neutral-800">
                                {index + 1}.
                            </p>
                            <div className="flex flex-col gap-1">
                                <p className="text-sm font-medium text-neutral-800">
                                    {student.nama}
                                </p>
                                <p className="text-sm font-medium text-neutral-800">
                                    <span className="font-normal">NIS: </span>
                                    {student.nis}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="pt-3 border-t border-slate-300">
                        {editingId === student.id ? (
                            <div className="flex flex-col gap-3">
                                <label className="block">
                                    <span className="text-xs text-neutral-500">
                                        Nama Siswa
                                    </span>
                                    <input
                                        type="text"
                                        name="nama"
                                        value={editData.nama}
                                        onChange={handleInputChange}
                                        className={`w-full p-2 mt-1 text-base border rounded-xl focus:outline-none ${
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
                                </label>
                                <label className="block">
                                    <span className="text-xs text-neutral-500">
                                        Nomor Induk Siswa
                                    </span>
                                    <input
                                        type="text"
                                        name="nis"
                                        value={editData.nis}
                                        onChange={(e) => {
                                            if (/^\d*$/.test(e.target.value)) {
                                                handleInputChange(e);
                                            }
                                        }}
                                        className={`w-full p-2 mt-1 text-base border rounded-xl focus:outline-none ${
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
                                </label>
                                <div className="flex justify-end gap-2 mt-4">
                                    <ButtonRounded
                                        size="sm"
                                        variant="outline"
                                        onClick={handleCancelEdit}
                                    >
                                        <X size={16} className="mr-1" />
                                        Batal
                                    </ButtonRounded>
                                    <ButtonRounded
                                        size="sm"
                                        onClick={(e) =>
                                            handleUpdate(e, student.id)
                                        }
                                    >
                                        <Save size={16} className="mr-1" />
                                        Simpan
                                    </ButtonRounded>
                                </div>
                            </div>
                        ) : (
                            <div className="flex justify-end gap-2">
                                <ButtonRounded
                                    size="sm"
                                    variant="outline"
                                    onClick={() => handleEditClick(student)}
                                >
                                    <Edit size={16} className="mr-1" />
                                    Edit
                                </ButtonRounded>
                                <ButtonRounded
                                    size="sm"
                                    variant="primary"
                                    onClick={(e) => handleDelete(e, student.id)}
                                >
                                    <Trash2 size={16} className="mr-1" />
                                    Hapus
                                </ButtonRounded>
                            </div>
                        )}
                    </div>
                </div>
            ))}
        </div>
    );
};

export default ShowSiswaCard;
