import React from "react";
import { Link } from "@inertiajs/react";
import { ChevronRight } from "lucide-react";

// Hapus 'pageTitle' dari props
const BreadcrumbNav = ({ items }) => {
    return (
        <div className="px-3 md:px-7 h-[120px] bg-sky-500 rounded-b-4xl shadow-lg flex items-start">
            <nav className="flex items-center space-x-1 md:space-x-2 text-white text-sm">
                {items.map((item, index) => (
                    <React.Fragment key={index}>
                        {item.href ? (
                            <Link
                                href={item.href}
                                className="capitalize opacity-75 hover:opacity-100 transition-opacity"
                            >
                                {item.label}
                            </Link>
                        ) : (
                            <span className="capitalize">{item.label}</span>
                        )}

                        {index < items.length - 1 && (
                            <ChevronRight size={20} className="opacity-75" />
                        )}
                    </React.Fragment>
                ))}
            </nav>
        </div>
    );
};

export default BreadcrumbNav;
