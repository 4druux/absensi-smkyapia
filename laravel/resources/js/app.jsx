import "./bootstrap";
import "../css/app.css";

import { createRoot } from "react-dom/client";
import { createInertiaApp } from "@inertiajs/react";
import { Toaster } from "react-hot-toast";
import MainLayout from "./Layouts/MainLayout";

const pageTitles = {
    "/beranda": "Absensi SMK Yapia",
};

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => {
        const path = window.location.pathname;
        const pageTitle = pageTitles[path];
        return pageTitle || `${title} - ${appName}`;
    },
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.jsx", { eager: true });
        let page = pages[`./Pages/${name}.jsx`];

        if (page.default.layout === undefined) {
            page.default.layout = (page) => <MainLayout>{page}</MainLayout>;
        }

        return page;
    },
    progress: {
        color: "#4B5563",
    },
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <>
                <App {...props} />
                <Toaster
                    position="top-right"
                    reverseOrder={true}
                    duration={5000}
                    toastOptions={{ className: "custom-toast" }}
                />
            </>
        );
    },
});
