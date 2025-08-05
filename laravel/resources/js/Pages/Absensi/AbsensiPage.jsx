import MainLayout from "@/Layouts/MainLayout";
import AttendancePage from "@/Components/AttendancePage";

const AbsensiPage = ({
    studentData,
    tanggal,
    bulan,
    namaBulan,
    tahun,
    existingAttendance,
    tanggalAbsen,
    selectedClass,
}) => {
    return (
        <AttendancePage
            studentData={studentData}
            tanggal={tanggal}
            bulan={bulan}
            namaBulan={namaBulan}
            tahun={tahun}
            initialAttendance={existingAttendance}
            tanggalAbsen={tanggalAbsen}
            selectedClass={selectedClass}
        />
    );
};

AbsensiPage.layout = (page) => (
    <MainLayout
        children={page}
        title={`Absensi ${page.props.tanggal} ${page.props.namaBulan}`}
    />
);

export default AbsensiPage;
