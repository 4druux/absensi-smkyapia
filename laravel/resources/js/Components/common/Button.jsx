import { Link } from "@inertiajs/react";
import { cva } from "class-variance-authority";
import { clsx } from "clsx";

const variants = cva(
    "inline-flex items-center justify-center rounded-full text-sm font-medium transition-colors disabled:opacity-50 disabled:pointer-events-none cursor-pointer",
    {
        variants: {
            variant: {
                primary: "bg-sky-500 text-white hover:bg-sky-600 shadow-md",
                outline:
                    "border border-sky-600 text-sky-600 bg-transparent hover:bg-sky-600 hover:text-white",
                ghost: "hover:bg-sky-100 hover:text-sky-900",
            },
            size: {
                default: "h-11 px-6 py-3",
                sm: "h-9 px-4",
                lg: "h-12 px-8 text-base",
            },
        },
        defaultVariants: {
            variant: "primary",
            size: "default",
        },
    }
);

const Button = ({
    className,
    children,
    variant,
    size,
    as = "button",
    ...props
}) => {
    const Comp = as === "link" ? Link : "button";

    return (
        <Comp
            className={clsx(variants({ variant, size, className }))}
            {...props}
        >
            {children}
        </Comp>
    );
};

export default Button;
