import React from "react";
import MainLayout from "@/Layouts/MainLayout";
import StudentDataForm from "@/Components/StudentDataForm";
import BreadcrumbNav from "@/Components/common/BreadcrumbNav";

const InputData = () => {
    const breadcrumbItems = [
        { label: "Data Siswa", href: route("data-siswa.index") },
        { label: "Input Data Baru", href: null },
    ];

    return (
        <div>
            <BreadcrumbNav items={breadcrumbItems} />
            <div className="px-3 md:px-7 -mt-20 pb-10">
                <StudentDataForm />
            </div>
        </div>
    );
};

InputData.layout = (page) => (
    <MainLayout children={page} title="Input Data Siswa" />
);

export default InputData;
