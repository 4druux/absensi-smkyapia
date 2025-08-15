import { Save } from "lucide-react";
import ButtonRounded from "@/Components/common/button-rounded";

const ShowRekapCard = ({
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
                        <div className="flex flex-col gap-3">
                            <label className="block">
                                <span className="text-xs text-neutral-500">
                                    Poin Tambahan
                                </span>
                                <input
                                    type="text"
                                    inputMode="decimal"
                                    name="poin_tambahan"
                                    value={student.poin_tambahan}
                                    onChange={(e) =>
                                        handleInputChange(e, index)
                                    }
                                    onKeyPress={handleKeyPress}
                                    placeholder="0"
                                    className={`w-full p-2 mt-1 text-base border rounded-xl focus:outline-none border-slate-300 focus:border-sky-300`}
                                />
                            </label>
                            <label className="block">
                                <span className="text-xs text-neutral-500">
                                    Keterangan
                                </span>
                                <input
                                    type="text"
                                    name="keterangan"
                                    value={student.keterangan}
                                    onChange={(e) =>
                                        handleInputChange(e, index)
                                    }
                                    className={`w-full p-2 mt-1 text-base border rounded-xl focus:outline-none border-slate-300 focus:border-sky-300`}
                                />
                            </label>
                            <div className="flex justify-end gap-2 mt-4">
                                <ButtonRounded
                                    size="sm"
                                    onClick={() => handleSave(student)}
                                    disabled={isSaving[student.id]}
                                >
                                    <Save size={16} className="mr-1" />
                                    Simpan
                                </ButtonRounded>
                            </div>
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
};

export default ShowRekapCard;
