import ButtonRounded from "@/Components/common/button-rounded";
import { Trash2 } from "lucide-react";

const UserTable = ({ users, type, onApprove, onReject, isProcessing }) => {
    return (
        <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-slate-200">
                <thead className="bg-slate-50">
                    <tr>
                        <th className="w-16 px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            No
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Nama
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Email
                        </th>
                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Role
                        </th>
                        <th className="px-6 py-3 text-right text-xs font-medium text-neutral-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody className="bg-white divide-y divide-slate-200">
                    {users.map((user) => (
                        <tr
                            key={user.id}
                            className="transition-colors duration-150 even:bg-slate-50 hover:bg-slate-100"
                        >
                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900">
                                {users.indexOf(user) + 1}.
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-neutral-900">
                                {user.name}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                                {user.email}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                                {user.role}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div className="flex justify-end gap-2">
                                    {type === "pending" && (
                                        <>
                                            <ButtonRounded
                                                type="button"
                                                size="sm"
                                                variant="outline"
                                                onClick={() =>
                                                    onReject(user.id)
                                                }
                                                disabled={isProcessing}
                                            >
                                                <span className="text-sm">
                                                    Tolak
                                                </span>
                                            </ButtonRounded>
                                            <ButtonRounded
                                                type="button"
                                                size="sm"
                                                variant="primary"
                                                onClick={() =>
                                                    onApprove(user.id)
                                                }
                                                disabled={isProcessing}
                                            >
                                                <span className="text-sm">
                                                    Setujui
                                                </span>
                                            </ButtonRounded>
                                        </>
                                    )}
                                    {type === "approved" && (
                                        <ButtonRounded
                                            type="button"
                                            size="sm"
                                            variant="outline"
                                            onClick={() => onReject(user.id)}
                                            disabled={isProcessing}
                                        >
                                            <Trash2 size={16} />
                                        </ButtonRounded>
                                    )}
                                </div>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default UserTable;
