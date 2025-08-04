import React from "react";
import MainLayout from "@/Layouts/MainLayout";
import StudentDataForm from "@/Components/StudentDataForm";
import AttendancePage from "@/Components/AttendancePage";
import { useAppContext } from "@/Context/AppContext";

function HomePage() {
    const { currentPage, studentData, onSaveAndContinue } = useAppContext();

    const renderCurrentPage = () => {
        switch (currentPage) {
            case "input":
                return (
                    <StudentDataForm
                        onSaveAndContinue={onSaveAndContinue}
                        initialData={studentData || undefined}
                    />
                );
            case "attendance":
                return <AttendancePage studentData={studentData} />;

            default:
                return <div>Halaman tidak ditemukan</div>;
        }
    };

    return <div>{renderCurrentPage()}</div>;
}

HomePage.layout = (page) => <MainLayout children={page} />;

export default HomePage;
