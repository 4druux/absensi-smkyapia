import { Menu, User, Settings, LogIn, LogOut, FileText } from "lucide-react";
import { AnimatePresence, motion } from "framer-motion";
import { useDropdown, dropdownAnimation } from "@/hooks/use-dropdown";
import { usePage, Link, useForm } from "@inertiajs/react";
import { toast } from "react-hot-toast";

const Header = ({ onMenuClick }) => {
    const { isOpen, setIsOpen, dropdownRef } = useDropdown();
    const { auth = { user: null }, csrf_token } = usePage().props;

    const { post } = useForm({
        _token: csrf_token,
    });

    const handleLogout = (e) => {
        e.preventDefault();
        post(route("logout"), {
            replace: true,
            preserveState: false,
            onSuccess: () => {
                toast.success("Anda berhasil keluar!");
            },
            onError: () => {
                toast.error("Gagal keluar, silakan coba lagi.");
            },
        });
    };

    return (
        <header className="bg-sky-500 text-white sticky top-0 z-30">
            <div className="flex items-center justify-between py-4 md:p-4">
                <div className="flex items-center space-x-2">
                    <button
                        onClick={onMenuClick}
                        className="p-2 rounded-full hover:bg-sky-400/40 focus:outline-none cursor-pointer"
                        aria-label="Toggle Menu"
                    >
                        <Menu className="w-6 h-6" />
                    </button>
                    <h1 className="text-lg uppercase font-medium">
                        tkj {new Date().getFullYear()}
                    </h1>
                </div>

                {auth.user ? (
                    <div
                        ref={dropdownRef}
                        className="relative flex items-center"
                    >
                        <button
                            onClick={() => setIsOpen(!isOpen)}
                            className="p-2 rounded-full hover:bg-sky-400/40 focus:outline-none cursor-pointer"
                            aria-label="User Profile"
                        >
                            <User className="w-6 h-6" />
                        </button>

                        <AnimatePresence>
                            {isOpen && (
                                <motion.div
                                    initial="hidden"
                                    animate="visible"
                                    exit="hidden"
                                    variants={dropdownAnimation.variants}
                                    transition={dropdownAnimation.transition}
                                    className="absolute top-full right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-slate-200"
                                >
                                    <div
                                        className="py-1 px-2"
                                        role="menu"
                                        aria-orientation="vertical"
                                    >
                                        <div className="px-4 py-2 text-sm text-gray-900 border-b border-gray-100">
                                            <div className="font-medium">
                                                {auth.user.name}
                                            </div>
                                            <div className="text-xs text-gray-500">
                                                {auth.user.role}
                                            </div>
                                        </div>
                                        <a
                                            href="#"
                                            className="flex items-center gap-3 w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 rounded-md"
                                            role="menuitem"
                                        >
                                            <Settings className="w-4 h-4 text-gray-500" />
                                            <span>Pengaturan Akun</span>
                                        </a>
                                        <button
                                            onClick={handleLogout}
                                            className="flex items-center gap-3 w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md"
                                            role="menuitem"
                                        >
                                            <LogOut className="w-4 h-4" />
                                            <span>Keluar</span>
                                        </button>
                                    </div>
                                </motion.div>
                            )}
                        </AnimatePresence>
                    </div>
                ) : (
                    <div
                        ref={dropdownRef}
                        className="relative flex items-center"
                    >
                        <button
                            onClick={() => setIsOpen(!isOpen)}
                            className="p-2 rounded-full hover:bg-sky-400/40 focus:outline-none cursor-pointer"
                            aria-label="Login"
                        >
                            <User className="w-6 h-6" />
                        </button>

                        <AnimatePresence>
                            {isOpen && (
                                <motion.div
                                    initial="hidden"
                                    animate="visible"
                                    exit="hidden"
                                    variants={dropdownAnimation.variants}
                                    transition={dropdownAnimation.transition}
                                    className="absolute top-full right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-slate-200"
                                >
                                    <div className="py-1 px-2" role="menu">
                                        <Link
                                            href={route("login")}
                                            className="flex items-center gap-3 w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 rounded-md"
                                            role="menuitem"
                                        >
                                            <LogIn className="w-4 h-4 text-gray-500" />
                                            <span>Masuk</span>
                                        </Link>
                                        <Link
                                            href={route("register")}
                                            className="flex items-center gap-3 w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 rounded-md"
                                            role="menuitem"
                                        >
                                            <FileText className="w-4 h-4 text-gray-500" />
                                            <span>Daftar</span>
                                        </Link>
                                    </div>
                                </motion.div>
                            )}
                        </AnimatePresence>
                    </div>
                )}
            </div>
        </header>
    );
};

export default Header;
