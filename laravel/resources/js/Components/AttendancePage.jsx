import React, { useState, useEffect } from "react";
import {
    ClipboardCheck,
    Users,
    BookOpen,
    CheckCircle,
    AlertTriangle,
} from "lucide-react";

const AttendancePage = ({ studentData }) => {
    const [attendance, setAttendance] = useState({});

    if (!studentData) {
        return (
            <div>
                <div className="px-3 md:px-7 h-[120px] bg-sky-500 rounded-b-4xl shadow-lg">
                    <h3 className="text-white">Presensi</h3>
                </div>

                <div className="px-3 md:px-7 -mt-20">
                    <div className="bg-white rounded-2xl shadow-lg p-4 flex flex-col items-center justify-center space-y-2 py-20">
                        <AlertTriangle className="mx-auto h-12 w-12 text-yellow-400" />
                        <h3 className="mt-2 text-lg font-medium text-gray-700">
                            Data Siswa Kosong
                        </h3>
                        <p className="px-4 text-center text-sm text-gray-500">
                            Silakan input data siswa terlebih dahulu pada tab
                            'Input Data Siswa'.
                        </p>
                    </div>
                </div>
            </div>
        );
    }

    useEffect(() => {
        const initialAttendance = {};
        studentData.students.forEach((student) => {
            initialAttendance[student] = null;
        });
        setAttendance(initialAttendance);
    }, [studentData.students]);

    const handleAttendanceChange = (studentName, status) => {
        setAttendance((prev) => ({
            ...prev,
            [studentName]: prev[studentName] === status ? null : status,
        }));
    };

    const attendanceStatuses = [
        {
            key: "late",
            label: "Telat",
            color: "bg-yellow-100 text-yellow-800 border-yellow-300",
        },
        {
            key: "sick",
            label: "Sakit",
            color: "bg-blue-100 text-blue-800 border-blue-300",
        },
        {
            key: "permission",
            label: "Izin",
            color: "bg-green-100 text-green-800 border-green-300",
        },
        {
            key: "absent",
            label: "Alfa",
            color: "bg-red-100 text-red-800 border-red-300",
        },
        {
            key: "truant",
            label: "Bolos",
            color: "bg-purple-100 text-purple-800 border-purple-300",
        },
    ];

    const getAttendanceSummary = () => {
        const summary = {
            present: 0,
            late: 0,
            sick: 0,
            permission: 0,
            absent: 0,
            truant: 0,
            notMarked: 0,
        };

        Object.values(attendance).forEach((status) => {
            if (status === null) {
                summary.notMarked++;
            } else if (status === "late") {
                summary.late++;
            } else {
                summary[status]++;
            }
        });

        summary.present =
            studentData.students.length -
            summary.late -
            summary.sick -
            summary.permission -
            summary.absent -
            summary.truant -
            summary.notMarked;
        return summary;
    };

    const summary = getAttendanceSummary();

    return (
        <div>
            <div className="px-3 md:px-7 h-[120px] bg-sky-500 rounded-b-4xl shadow-lg">
                <h3 className="text-white">Presensi</h3>
            </div>
            <div className="px-3 md:px-7 -mt-14">
                <div className="bg-white shadow-lg rounded-2xl p-8 flex flex-col space-y-2">
                    <div className="flex items-center justify-between">
                        <div className="flex items-end space-x-3">
                            <div className="p-3 bg-sky-100 rounded-lg">
                                <ClipboardCheck className="w-6 h-6 text-sky-600" />
                            </div>
                            <div>
                                <h3 className="text-lg font-medium text-gray-700">
                                    Absensi Siswa
                                </h3>
                                <div className="flex items-center space-x-4">
                                    <div className="flex items-center space-x-2">
                                        <Users className="w-4 h-4 text-gray-500" />
                                        <span className="text-sm text-gray-600 font-medium">
                                            {studentData.classCode}
                                        </span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <BookOpen className="w-4 h-4 text-gray-500" />
                                        <span className="text-sm text-gray-600">
                                            {studentData.major}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="text-right">
                            <div className="text-sm text-gray-500">
                                Total Siswa
                            </div>
                            <div className="text-2xl font-bold text-blue-600">
                                {studentData.students.length}
                            </div>
                        </div>
                    </div>

                    {/* Summary Cards */}
                    <div className="grid grid-cols-2 md:grid-cols-6 gap-4 my-6">
                        <div className="bg-white rounded-lg shadow p-4">
                            <div className="text-sm text-gray-500">Hadir</div>
                            <div className="text-xl font-medium text-neutral-600">
                                {summary.present}
                            </div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4">
                            <div className="text-sm text-gray-500">Telat</div>
                            <div className="text-xl font-medium text-neutralw-600">
                                {summary.late}
                            </div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4">
                            <div className="text-sm text-gray-500">Alfa</div>
                            <div className="text-xl font-medium text-neutral600">
                                {summary.sick + summary.permission}
                            </div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4">
                            <div className="text-sm text-gray-500">Sakit</div>
                            <div className="text-xl font-medium text-neutral600">
                                {summary.sick}
                            </div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4">
                            <div className="text-sm text-gray-500">Izin</div>
                            <div className="text-xl font-medium text-neutral600">
                                {summary.permission}
                            </div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4">
                            <div className="text-sm text-gray-500">
                                Tidak Hadir
                            </div>
                            <div className="text-xl font-medium text-neutral00">
                                {summary.absent + summary.truant}
                            </div>
                        </div>
                    </div>

                    {/* Attendance Table */}
                    <div className="bg-white rounded-lg overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h2 className="text-lg font-semibold text-gray-900">
                                Daftar Kehadiran
                            </h2>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            No
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nama Siswa
                                        </th>
                                        <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status Kehadiran
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {studentData.students.map(
                                        (student, index) => (
                                            <tr
                                                key={student}
                                                className="hover:bg-gray-50 transition-colors duration-150"
                                            >
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                                    {index + 1}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="flex items-center">
                                                        <div className="text-sm font-medium text-gray-900">
                                                            {student}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="flex flex-wrap justify-center gap-2">
                                                        {attendanceStatuses.map(
                                                            ({
                                                                key,
                                                                label,
                                                                color,
                                                            }) => (
                                                                <label
                                                                    key={key}
                                                                    className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium cursor-pointer transition-all duration-200 border ${
                                                                        attendance[
                                                                            student
                                                                        ] ===
                                                                        key
                                                                            ? `${color} ring-2 ring-offset-1 ring-blue-300`
                                                                            : "bg-gray-100 text-gray-500 border-gray-300 hover:bg-gray-200"
                                                                    }`}
                                                                >
                                                                    <input
                                                                        type="checkbox"
                                                                        checked={
                                                                            attendance[
                                                                                student
                                                                            ] ===
                                                                            key
                                                                        }
                                                                        onChange={() =>
                                                                            handleAttendanceChange(
                                                                                student,
                                                                                key
                                                                            )
                                                                        }
                                                                        className="sr-only"
                                                                    />
                                                                    {attendance[
                                                                        student
                                                                    ] ===
                                                                        key && (
                                                                        <CheckCircle className="w-3 h-3 mr-1" />
                                                                    )}
                                                                    {label}
                                                                </label>
                                                            )
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        )
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default AttendancePage;
