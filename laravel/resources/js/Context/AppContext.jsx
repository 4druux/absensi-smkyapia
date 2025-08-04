import React, { createContext, useState, useContext } from "react";

const AppContext = createContext();

export const AppProvider = ({ children }) => {
    const [currentPage, setCurrentPage] = useState("input");
    const [studentData, setStudentData] = useState(null);

    const handleSaveStudentData = (data) => {
        setStudentData(data);
        setCurrentPage("attendance");
    };

    const handlePageChange = (page) => {
        setCurrentPage(page);
    };

    const value = {
        currentPage,
        studentData,
        hasStudentData: !!studentData,
        onPageChange: handlePageChange,
        onSaveAndContinue: handleSaveStudentData,
    };

    return <AppContext.Provider value={value}>{children}</AppContext.Provider>;
};

export const useAppContext = () => {
    return useContext(AppContext);
};
