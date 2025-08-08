import MainLayout from "@/Layouts/MainLayout";
import BreadcrumbNav from "@/Components/ui/breadcrumb-nav";

const InputData = () => {
    const breadcrumbItems = [
        { label: "Data Siswa", href: route("data-siswa.index") },
        { label: "Input Data Baru", href: null },
    ];

    return (
        <div>
            <BreadcrumbNav items={breadcrumbItems} />
            <div className="px-3 md:px-7 -mt-20 pb-10">
                <h3>HomePage</h3>
            </div>
        </div>
    );
};

InputData.layout = (page) => (
    <MainLayout children={page} title="Input Data Siswa" />
);

export default InputData;
