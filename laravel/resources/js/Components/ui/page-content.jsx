import BreadcrumbNav from "@/Components/ui/breadcrumb-nav";

const PageContent = ({ children, breadcrumbItems, pageClassName }) => {
    return (
        <div>
            <BreadcrumbNav items={breadcrumbItems} />
            <div className={`px-3 md:px-7 pb-10 ${pageClassName}`}>
                <div className="bg-white shadow-lg rounded-2xl p-4 md:p-8">
                    {children}
                </div>
            </div>
        </div>
    );
};

export default PageContent;
