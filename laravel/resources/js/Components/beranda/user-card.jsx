import ButtonRounded from "@/Components/common/button-rounded";
import { LockKeyholeOpen, Mail, Trash2, User } from "lucide-react";

const UserCard = ({ users, type, onApprove, onReject, isProcessing }) => {
    return (
        <div className="grid grid-cols-1 gap-4">
            {users.map((user) => (
                <div
                    key={user.id}
                    className="flex flex-col gap-3 p-4 border rounded-xl border-slate-300"
                >
                    <div className="flex flex-col">
                        <div className="flex flex-col gap-2">
                            <div className="flex items-center gap-2">
                                <User className="w-4 h-5 md:w-5 md:h-5" />
                                <p className="text-sm font-medium text-neutral-700">
                                    {user.name}
                                </p>
                            </div>
                            <div className="flex items-center gap-2">
                                <Mail className="w-4 h-5 md:w-5 md:h-5" />
                                <p className="text-sm font-medium text-neutral-700">
                                    {user.email}
                                </p>
                            </div>
                            <div className="flex items-center gap-2">
                                <LockKeyholeOpen className="w-4 h-5 md:w-5 md:h-5" />
                                <p className="text-sm font-medium text-neutral-700">
                                    {user.role}
                                </p>
                            </div>
                        </div>

                        <div className="flex justify-end gap-2">
                            {type === "pending" && (
                                <>
                                    <ButtonRounded
                                        type="button"
                                        size="sm"
                                        variant="primary"
                                        onClick={() => onApprove(user.id)}
                                        disabled={isProcessing}
                                    >
                                        <span className="text-xs">Setujui</span>
                                    </ButtonRounded>
                                    <ButtonRounded
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        onClick={() => onReject(user.id)}
                                        disabled={isProcessing}
                                    >   
                                        <span className="text-xs">Tolak</span>
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
                    </div>
                </div>
            ))}
        </div>
    );
};

export default UserCard;
