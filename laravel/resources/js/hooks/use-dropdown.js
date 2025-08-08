import { useState, useEffect, useRef } from "react";

export const dropdownAnimation = {
    variants: {
        hidden: { opacity: 0, y: -5 },
        visible: { opacity: 1, y: 0 },
    },
    transition: { duration: 0.2, ease: "easeOut" },
};

export const useDropdown = () => {
    const [isOpen, setIsOpen] = useState(false);
    const dropdownRef = useRef(null);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (
                dropdownRef.current &&
                !dropdownRef.current.contains(event.target)
            ) {
                setIsOpen(false);
            }
        };

        if (isOpen) {
            document.addEventListener("mousedown", handleClickOutside);
        }

        return () => {
            document.removeEventListener("mousedown", handleClickOutside);
        };
    }, [isOpen]);

    return {
        isOpen,
        setIsOpen,
        dropdownRef,
    };
};
