import ButtonRounded from "@/Components/common/button-rounded";
import { Trash2 } from "lucide-react";

const UserTable = ({ users, type, onApprove, onReject, isProcessing }) => {
    return (
        <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                    <tr>
                        <th
                            scope="col"
                            className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Nama
                        </th>
                        <th
                            scope="col"
                            className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Email
                        </th>
                        <th
                            scope="col"
                            className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Role
                        </th>
                        <th
                            scope="col"
                            className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                    {users.map((user) => (
                        <tr key={user.id}>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {user.name}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {user.email}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
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
