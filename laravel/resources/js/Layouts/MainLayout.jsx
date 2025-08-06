import Header from "@/Components/Header";
import Sidebar from "@/Components/Sidebar";
import { useState, useEffect } from "react";
import { router } from "@inertiajs/react";

export default function MainLayout({ children, title }) {
    const [isSidebarOpen, setIsSidebarOpen] = useState(
        typeof window !== "undefined" && window.innerWidth >= 1280
    );

    const toggleSidebar = () => {
        setIsSidebarOpen(!isSidebarOpen);
    };

    useEffect(() => {
        const handleStartNavigation = () => {
            if (window.innerWidth < 1280) {
                setIsSidebarOpen(false);
            }
        };

        const removeEventListener = router.on("start", handleStartNavigation);

        return () => {
            removeEventListener();
        };
    }, []);

    return (
        <div className="min-h-screen bg-slate-50">
            <Sidebar isOpen={isSidebarOpen} />

            <div
                onClick={toggleSidebar}
                className={`
                    fixed inset-0 bg-black/40 z-40 transition-opacity duration-300
                    ${
                        isSidebarOpen && window.innerWidth < 1280
                            ? "opacity-100 pointer-events-auto"
                            : "opacity-0 pointer-events-none"
                    }
                    xl:hidden
                `}
            />

            <div
                className={`transition-all duration-300 ease-in-out ${
                    isSidebarOpen ? "xl:ml-80" : "xl:ml-0"
                }`}
            >
                <Header onMenuClick={toggleSidebar} pageTitle={title} />
                <main>{children}</main>
            </div>
        </div>
    );
}
