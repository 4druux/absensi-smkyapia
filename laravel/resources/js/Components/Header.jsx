import { Menu, User, Settings, LogOut } from "lucide-react";
import { AnimatePresence, motion } from "framer-motion";
import { useDropdown, dropdownAnimation } from "@/hooks/use-dropdown";

const Header = ({ onMenuClick }) => {
    const { isOpen, setIsOpen, dropdownRef } = useDropdown();

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
                        smk yapia parung
                    </h1>
                </div>

                <div ref={dropdownRef} className="relative flex items-center">
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
                                    <a
                                        href="#"
                                        className="flex items-center gap-3 w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 rounded-md"
                                        role="menuitem"
                                    >
                                        <Settings className="w-4 h-4 text-gray-500" />
                                        <span>Pengaturan Akun</span>
                                    </a>
                                    <a
                                        href="#"
                                        className="flex items-center gap-3 w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md"
                                        role="menuitem"
                                    >
                                        <LogOut className="w-4 h-4" />
                                        <span>Keluar</span>
                                    </a>
                                </div>
                            </motion.div>
                        )}
                    </AnimatePresence>
                </div>
            </div>
        </header>
    );
};

export default Header;
