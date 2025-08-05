import { Link } from "@inertiajs/react";
import { cva } from "class-variance-authority";
import { clsx } from "clsx";

const cardVariants = cva(
    "p-6 border rounded-xl transition-all duration-200 cursor-pointer text-center group",
    {
        variants: {
            variant: {
                default:
                    "bg-slate-50 hover:bg-sky-100 border-slate-200 hover:border-sky-300",
                active: "bg-sky-100 border-sky-300",
                success: "bg-green-100 border-green-300",
                warning: "bg-yellow-100 border-yellow-300",
            },
        },
        defaultVariants: {
            variant: "default",
        },
    }
);

const iconVariants = cva(
    "w-12 h-12 text-sky-500 mx-auto transition-transform duration-200 group-hover:scale-105 mb-2"
);

const ContentCard = ({
    href,
    title,
    subtitle,
    icon: Icon,
    variant = "default",
    children,
    className,
}) => {
    const Component = href ? Link : "div";

    const isNumericTitle = !isNaN(parseFloat(title)) && isFinite(title);

    const titleSizeClass = isNumericTitle
        ? "text-lg md:text-xl"
        : "text-md md:text-lg";

    const titleTextColor =
        variant === "success" ? "text-green-600" : "text-sky-600";
    const subtitleTextColor =
        variant === "success" ? "text-green-600" : "text-gray-500";

    return (
        <Component
            href={href}
            className={clsx(cardVariants({ variant, className }))}
        >
            <div className="relative">
                {children}
                {Icon && <Icon className={iconVariants()} />}
                {title && (
                    <h4
                        className={clsx(
                            "font-medium",
                            titleSizeClass,
                            titleTextColor
                        )}
                    >
                        {title}
                    </h4>
                )}
                {subtitle && (
                    <p className={clsx("text-sm", subtitleTextColor)}>
                        {subtitle}
                    </p>
                )}
            </div>
        </Component>
    );
};

export default ContentCard;
