import React from "react";
import { Menu, User } from "lucide-react";

const Header = ({ onMenuClick }) => {
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

                <div className="flex items-center">
                    <button
                        className="p-2 rounded-full hover:bg-sky-400/40 focus:outline-none cursor-pointer"
                        aria-label="User Profile"
                    >
                        <User className="w-6 h-6" />
                    </button>
                </div>
            </div>
        </header>
    );
};

export default Header;
