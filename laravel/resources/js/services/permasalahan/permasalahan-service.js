import api from "@/utils/api";

export const getPermasalahanClasses = async () => {
    return await api.get("/permasalahan/classes");
};

export const getPermasalahanYears = async () => {
    return await api.get("/permasalahan/years");
};

export const storePermasalahanYear = async () => {
    return await api.post("/permasalahan/years");
};

export const storeClassProblem = async (payload) => {
    return await api.post("/permasalahan/class-problems", payload);
};

export const deleteClassProblem = async (id) => {
    return await api.delete(`/permasalahan/class-problems/${id}`);
};

export const storeStudentProblem = async (payload) => {
    return await api.post("/permasalahan/student-problems", payload);
};

export const deleteStudentProblem = async (id) => {
    return await api.delete(`/permasalahan/student-problems/${id}`);
};
