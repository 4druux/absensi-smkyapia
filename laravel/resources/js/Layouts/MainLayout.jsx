import React, { useState } from "react";
import Header from "@/Components/Header";
import Sidebar from "@/Components/Sidebar";

export default function MainLayout({ children }) {
    const [isSidebarOpen, setSidebarOpen] = useState(true);

    return (
        <div className="min-h-screen bg-gray-100">
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
                style={{ overflow: "auto", height: "100dvh" }}
            >
                <Header onMenuClick={() => setSidebarOpen(!isSidebarOpen)} />
                <main className="flex-1">{children}</main>
            </div>
        </div>
    );
}
