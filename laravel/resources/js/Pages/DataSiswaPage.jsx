import React from "react";
import MainLayout from "@/Layouts/MainLayout";
import StudentDataForm from "@/Components/StudentDataForm";
import { useAppContext } from "@/Context/AppContext";

function DataSiswaPage({ studentData }) {
    const { onSaveAndContinue } = useAppContext();
    return (
        <div>
            <StudentDataForm
                onSaveAndContinue={onSaveAndContinue}
                initialData={studentData || undefined}
            />
        </div>
    );
}

DataSiswaPage.layout = (page) => <MainLayout children={page} />;

export default DataSiswaPage;
