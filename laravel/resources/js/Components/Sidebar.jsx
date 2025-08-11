import { useEffect, useState } from "react";
import { Users, ClipboardCheck, FileText, ChevronDown } from "lucide-react";
import { CiBank } from "react-icons/ci";
import { Link } from "@inertiajs/react";
import { motion, AnimatePresence } from "framer-motion";

const Sidebar = ({ isOpen }) => {
    const [openSubMenu, setOpenSubMenu] = useState(null);

    useEffect(() => {
        const handleResize = () => {
            if (window.innerWidth >= 1280) {
                document.body.style.overflow = "unset";
            } else {
                if (isOpen) {
                    document.body.style.overflow = "hidden";
                } else {
                    document.body.style.overflow = "unset";
                }
            }
        };

        window.addEventListener("resize", handleResize);
        handleResize(); 

        return () => window.removeEventListener("resize", handleResize);
    }, [isOpen]);

    const menuItems = [
        {
            id: "data-siswa",
            label: "Data Kelas & Siswa",
            icon: Users,
            description: "Kelola data kelas & siswa",
            href: "/data-siswa",
        },
        {
            id: "absensi",
            label: "Absensi",
            icon: ClipboardCheck,
            description: "Kelola absensi siswa",
            href: "/absensi",
        },
        {
            id: "uang-kas",
            label: "Uang Kas",
            icon: CiBank,
            description: "Kelola uang kas",
            href: "/uang-kas",
        },
        {
            id: "laporan",
            label: "Laporan",
            icon: FileText,
            description: "Kelola laporan tahunan",
            subMenu: [
                {
                    id: "rekapitulasi",
                    label: "Rekapitulasi",
                    href: "/rekapitulasi",
                },
                {
                    id: "grafik",
                    label: "Grafik Absensi",
                    href: "/grafik",
                },
                {
                    id: "kenaikan-bersyarat",
                    label: "Kenaikan Bersyarat",
                    href: "/kenaikan-bersyarat",
                },
                {
                    id: "permasalahan",
                    label: "Permasalahan Kelas & Siswa",
                    href: "/permasalahan",
                },
            ],
        },
    ];

    const listVariants = {
        visible: {
            transition: {
                staggerChildren: 0.1,
                delayChildren: 0.2,
            },
        },
        hidden: {},
    };

    const itemVariants = {
        visible: {
            opacity: 1,
            x: 0,
            transition: {
                type: "spring",
                stiffness: 100,
                damping: 12,
            },
        },
        hidden: {
            opacity: 0,
            x: -20,
        },
    };

    const footerVariants = {
        visible: {
            opacity: 1,
            y: 0,
            transition: {
                type: "spring",
                stiffness: 100,
                damping: 12,
                delay: 0.5,
            },
        },
        hidden: {
            opacity: 0,
            y: 20,
        },
    };

    return (
        <aside
            className={`
                fixed top-0 left-0 h-screen bg-white shadow-lg rounded-r-2xl xl:rounded-none transition-transform duration-300 ease-in-out z-50
                ${isOpen ? "translate-x-0" : "-translate-x-full"}
            w-72 xl:w-80`}
        >
            <div className="xl:w-80 h-full flex flex-col">
                <div className="py-6 px-4 md:p-6 border-b border-slate-200">
                    <div className="flex items-center gap-2 md:gap-4">
                        <img
                            src="/images/logo-smk.png"
                            alt="Logo"
                            className="w-10 md:w-14 object-cover"
                        />
                        <div>
                            <h1 className="text-md md:text-lg uppercase font-medium text-neutral-700">
                                tkj
                            </h1>
                            <p className="text-xs md:text-sm text-neutral-600">
                                Manajemen Siswa
                            </p>
                        </div>
                    </div>
                </div>

                <nav className="flex-1 p-3 md:p-5 overflow-y-auto">
                    <AnimatePresence>
                        {isOpen && (
                            <motion.ul
                                className="space-y-2"
                                initial="hidden"
                                animate="visible"
                                exit="hidden"
                                variants={listVariants}
                            >
                                {menuItems.map((item) => {
                                    const Icon = item.icon;
                                    const isParentActive =
                                        item.subMenu &&
                                        item.subMenu.some((subItem) =>
                                            window.location.pathname.startsWith(
                                                subItem.href
                                            )
                                        );

                                    if (item.subMenu) {
                                        return (
                                            <motion.li
                                                key={item.id}
                                                variants={itemVariants}
                                            >
                                                <div
                                                    className={`w-full flex items-center justify-between p-4 rounded-2xl transition-all duration-200 text-left cursor-pointer group ${
                                                        isParentActive
                                                            ? "bg-sky-100"
                                                            : "text-neutral-600 hover:bg-neutral-50 hover:text-neutral-800"
                                                    }`}
                                                    onClick={() =>
                                                        setOpenSubMenu(
                                                            openSubMenu ===
                                                                item.id
                                                                ? null
                                                                : item.id
                                                        )
                                                    }
                                                >
                                                    <div className="flex items-center space-x-2 md:space-x-3">
                                                        <Icon
                                                            className={`w-8 h-8 ${
                                                                isParentActive
                                                                    ? "text-sky-600"
                                                                    : "text-neutral-600"
                                                            }`}
                                                        />
                                                        <div className="flex-1">
                                                            <div
                                                                className={`font-medium text-sm ${
                                                                    isParentActive
                                                                        ? "text-sky-600"
                                                                        : ""
                                                                }`}
                                                            >
                                                                {item.label}
                                                            </div>
                                                            <div
                                                                className={`text-xs ${
                                                                    isParentActive
                                                                        ? "text-sky-600"
                                                                        : "text-neutral-500 group-hover:text-neutral-800"
                                                                }`}
                                                            >
                                                                {
                                                                    item.description
                                                                }
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <ChevronDown
                                                        className={`w-5 h-5 transition-transform duration-200 ${
                                                            openSubMenu ===
                                                            item.id
                                                                ? "rotate-180"
                                                                : ""
                                                        } ${
                                                            isParentActive
                                                                ? "text-sky-600"
                                                                : "text-neutral-600"
                                                        }`}
                                                    />
                                                </div>
                                                <AnimatePresence>
                                                    {openSubMenu ===
                                                        item.id && (
                                                        <motion.ul
                                                            initial={{
                                                                height: 0,
                                                                opacity: 0,
                                                                marginTop: 0,
                                                            }}
                                                            animate={{
                                                                height: "auto",
                                                                opacity: 1,
                                                                marginTop:
                                                                    "0.5rem",
                                                            }}
                                                            exit={{
                                                                height: 0,
                                                                opacity: 0,
                                                                marginTop: 0,
                                                            }}
                                                            className="pl-8 space-y-1 overflow-hidden"
                                                        >
                                                            {item.subMenu.map(
                                                                (subItem) => {
                                                                    const isSubActive =
                                                                        window.location.pathname.startsWith(
                                                                            subItem.href
                                                                        );
                                                                    return (
                                                                        <li
                                                                            key={
                                                                                subItem.id
                                                                            }
                                                                        >
                                                                            <Link
                                                                                href={
                                                                                    subItem.href
                                                                                }
                                                                                className={`w-full flex p-3 items-center rounded-xl transition-all duration-200 text-left ${
                                                                                    isSubActive
                                                                                        ? "bg-sky-100 text-sky-600 font-medium"
                                                                                        : "text-neutral-600 hover:bg-neutral-50 hover:text-neutral-800"
                                                                                }`}
                                                                            >
                                                                                <span className="text-sm">
                                                                                    {
                                                                                        subItem.label
                                                                                    }
                                                                                </span>
                                                                            </Link>
                                                                        </li>
                                                                    );
                                                                }
                                                            )}
                                                        </motion.ul>
                                                    )}
                                                </AnimatePresence>
                                            </motion.li>
                                        );
                                    }

                                    const isActive =
                                        window.location.pathname.startsWith(
                                            item.href
                                        );
                                    return (
                                        <motion.li
                                            key={item.id}
                                            variants={itemVariants}
                                        >
                                            <Link
                                                href={item.href}
                                                className={`w-full flex items-center space-x-2 md:space-x-3 p-4 rounded-2xl transition-all duration-200 text-left cursor-pointer group ${
                                                    isActive
                                                        ? "bg-sky-100"
                                                        : "text-neutral-600 hover:bg-neutral-50 hover:text-neutral-800"
                                                }`}
                                            >
                                                <Icon
                                                    className={`w-8 h-8 ${
                                                        isActive
                                                            ? "text-sky-600"
                                                            : "text-neutral-600"
                                                    }`}
                                                />
                                                <div className="flex-1">
                                                    <div
                                                        className={`font-medium text-sm ${
                                                            isActive
                                                                ? "text-sky-600"
                                                                : ""
                                                        }`}
                                                    >
                                                        {item.label}
                                                    </div>
                                                    <div
                                                        className={`text-xs ${
                                                            isActive
                                                                ? "text-sky-600"
                                                                : "text-neutral-500 group-hover:text-neutral-800"
                                                        }`}
                                                    >
                                                        {item.description}
                                                    </div>
                                                </div>
                                            </Link>
                                        </motion.li>
                                    );
                                })}
                            </motion.ul>
                        )}
                    </AnimatePresence>
                </nav>

                <AnimatePresence>
                    {isOpen && (
                        <div className="p-4 border-t border-slate-200">
                            <motion.div
                                initial="hidden"
                                animate="visible"
                                exit="hidden"
                                variants={footerVariants}
                                className="text-xs uppercase text-neutral-500 text-center"
                            >
                                tkj &copy; {new Date().getFullYear()}
                            </motion.div>
                        </div>
                    )}
                </AnimatePresence>
            </div>
        </aside>
    );
};

export default Sidebar;
