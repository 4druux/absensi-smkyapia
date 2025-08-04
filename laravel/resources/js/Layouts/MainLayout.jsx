import React, { useState } from "react";
import Header from "@/Components/Header";
import Sidebar from "@/Components/Sidebar";

export default function MainLayout({ children }) {
    const [isSidebarOpen, setSidebarOpen] = useState(true);

    return (
        <div className="min-h-screen bg-neutral-100 flex">
            <Sidebar isOpen={isSidebarOpen} pageProps={children.props} />
            {isSidebarOpen && (
                <div
                    className="fixed inset-0 bg-black/40 z-40 md:hidden"
                    onClick={() => setSidebarOpen(false)}
                />
            )}
            <div
                className={`
                    flex flex-col flex-1 transition-all duration-300 ease-in-out
                    ${isSidebarOpen ? "pl-0 md:pl-80" : "pl-0"}
                `}
            >
                <Header onMenuClick={() => setSidebarOpen(!isSidebarOpen)} />
                <main className="flex-1">{children}</main>
            </div>
        </div>
    );
}
