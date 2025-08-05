import { Edit, Trash2, Save, X } from "lucide-react";
import Button from "@/Components/common/Button";

const ShowSiswaCard = ({
    students,
    editingId,
    editData,
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
                    className="p-4 space-y-3 border rounded-xl border-neutral-300"
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
                                    {student.nis}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="pt-3 border-t border-neutral-300">
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
                                        className="w-full p-2 mt-1 text-base border border-neutral-300 focus:border-sky-300 rounded-xl focus:outline-none"
                                    />
                                </label>
                                <label className="block">
                                    <span className="text-xs text-neutral-500">
                                        Nomor Induk Siswa
                                    </span>
                                    <input
                                        type="text"
                                        name="nis"
                                        value={editData.nis}
                                        onChange={handleInputChange}
                                        className="w-full p-2 mt-1 text-base border border-neutral-300 focus:border-sky-300 rounded-xl focus:outline-none"
                                    />
                                </label>
                                <div className="flex justify-end gap-2 mt-4">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        onClick={handleCancelEdit}
                                    >
                                        <X size={16} className="mr-1" />
                                        Batal
                                    </Button>
                                    <Button
                                        size="sm"
                                        onClick={(e) =>
                                            handleUpdate(e, student.id)
                                        }
                                    >
                                        <Save size={16} className="mr-1" />
                                        Simpan
                                    </Button>
                                </div>
                            </div>
                        ) : (
                            <div className="flex justify-end gap-2">
                                <Button
                                    size="sm"
                                    variant="outline"
                                    onClick={() => handleEditClick(student)}
                                >
                                    <Edit size={16} className="mr-1" />
                                    Edit
                                </Button>
                                <Button
                                    size="sm"
                                    variant="primary"
                                    onClick={(e) => handleDelete(e, student.id)}
                                >
                                    <Trash2 size={16} className="mr-1" />
                                    Hapus
                                </Button>
                            </div>
                        )}
                    </div>
                </div>
            ))}
        </div>
    );
};

export default ShowSiswaCard;
