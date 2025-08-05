import React from "react";
import { Users, ClipboardCheck } from "lucide-react";
import { Link } from "@inertiajs/react";

const Sidebar = ({ isOpen }) => {
    const menuItems = [
        {
            id: "data-siswa",
            label: "Data Siswa",
            icon: Users,
            description: "Kelola data siswa",
            href: "/data-siswa",
        },
        {
            id: "absensi",
            label: "Absensi",
            icon: ClipboardCheck,
            description: "Kelola absensi siswa",
            href: "/absensi",
        },
    ];

    return (
        <aside
            className={`
                fixed top-0 left-0 h-screen bg-white shadow-lg rounded-r-2xl xl:rounded-none transition-transform duration-300 ease-in-outm z-50
                ${isOpen ? "translate-x-0" : "-translate-x-full"}
            `}
        >
            <div className="xl:w-80 h-full flex flex-col">
                {/* Header */}
                <div className="py-6 px-4 md:p-6 border-b border-neutral-200">
                    <div className="flex items-center gap-2 md:gap-4">
                        <img
                            src="/images/logo-smk.png"
                            alt="Logo"
                            className="w-10 md:w-14 object-cover"
                        />
                        <div>
                            <h1 className="text-md md:text-lg uppercase font-semibold text-neutral-700">
                                Sistem Absensi
                            </h1>
                            <p className="text-xs md:text-sm text-neutral-600">
                                Kelola kehadiran siswa
                            </p>
                        </div>
                    </div>
                </div>

                <nav className="flex-1 p-3 md:p-5">
                    <ul className="space-y-2">
                        {menuItems.map((item) => {
                            const Icon = item.icon;
                            const isActive =
                                window.location.pathname === item.href;

                            return (
                                <li key={item.id}>
                                    <Link
                                        href={item.href}
                                        className={`w-full flex items-center space-x-2 md:space-x-3 p-4 rounded-2xl transition-all duration-200 text-left cursor-pointer ${
                                            isActive
                                                ? "bg-sky-100"
                                                : "text-neutral-700 hover:bg-neutral-50 hover:text-neutral-900"
                                        }`}
                                    >
                                        <Icon
                                            className={`w-5 h-5 ${
                                                isActive
                                                    ? "text-sky-600"
                                                    : "text-neutral-500"
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
                                                        : "text-neutral-500"
                                                }`}
                                            >
                                                {item.description}
                                            </div>
                                        </div>
                                    </Link>
                                </li>
                            );
                        })}
                    </ul>
                </nav>

                {/* Footer */}
                <div className="p-4 border-t border-neutral-200">
                    <div className="text-xs text-neutral-500 text-center">
                        Sistem Absensi v1.0
                    </div>
                </div>
            </div>
        </aside>
    );
};

export default Sidebar;
