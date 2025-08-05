import { useState } from "react";
import MainLayout from "@/Layouts/MainLayout";
import { router } from "@inertiajs/react";
import { ArrowLeft } from "lucide-react";
import toast from "react-hot-toast";
import Button from "@/Components/common/Button";
import BreadcrumbNav from "@/Components/common/BreadcrumbNav";
import ShowSiswaTable from "@/Components/siswa/ShowSiswaTable";
import ShowSiswaCard from "@/Components/siswa/ShowSiswaCard";

const ShowClass = ({ students, selectedClass }) => {
    const [editingId, setEditingId] = useState(null);
    const [editData, setEditData] = useState({});

    const breadcrumbItems = [
        { label: "Data Siswa", href: route("data-siswa.index") },
        {
            label: `${selectedClass.kelas} - ${selectedClass.jurusan}`,
            href: null,
        },
    ];

    const handleEditClick = (student) => {
        setEditingId(student.id);
        setEditData({
            nama: student.nama,
            nis: student.nis,
            kelas: student.kelas,
            jurusan: student.jurusan,
        });
    };

    const handleCancelEdit = () => {
        setEditingId(null);
        setEditData({});
    };

    const handleUpdate = (e, id) => {
        e.preventDefault();

        const payload = {
            _method: "put",
            ...editData,
            nama: editData.nama,
            nis: editData.nis,
            kelas: selectedClass.kelas,
            jurusan: selectedClass.jurusan,
        };

        router.post(route("data-siswa.student.update", { id: id }), payload, {
            onSuccess: () => {
                toast.success("Data siswa berhasil diperbarui!");
                setEditingId(null);
            },
            onError: (errors) => {
                toast.error("Gagal memperbarui data.");
                console.error("Update Errors:", errors);
            },
        });
    };

    const handleDelete = (e, id) => {
        e.preventDefault();

        if (confirm("Apakah Anda yakin ingin menghapus siswa ini?")) {
            router.post(
                route("data-siswa.student.destroy", { id: id }),
                {
                    _method: "delete",
                },
                {
                    onSuccess: () => {
                        toast.success("Siswa berhasil dihapus!");
                    },
                    onError: (errors) => {
                        toast.error("Gagal menghapus siswa.");
                        console.error(
                            "Errors from server during delete:",
                            errors
                        );
                    },
                }
            );
        }
    };

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setEditData((prev) => ({ ...prev, [name]: value }));
    };

    return (
        <div>
            <BreadcrumbNav items={breadcrumbItems} />
            <div className="px-3 md:px-7 -mt-20 pb-10">
                <div className="bg-white shadow-lg rounded-2xl p-4 md:p-8">
                    <div className="px-1 py-4">
                        <h2 className="text-lg text-neutral-800">
                            Daftar Siswa Kelas {selectedClass.kelas} -{" "}
                            {selectedClass.jurusan}
                        </h2>
                    </div>

                    <div className="hidden lg:block">
                        <ShowSiswaTable
                            students={students}
                            editingId={editingId}
                            editData={editData}
                            handleEditClick={handleEditClick}
                            handleUpdate={handleUpdate}
                            handleDelete={handleDelete}
                            handleCancelEdit={handleCancelEdit}
                            handleInputChange={handleInputChange}
                        />
                    </div>

                    <div className="lg:hidden">
                        <ShowSiswaCard
                            students={students}
                            editingId={editingId}
                            editData={editData}
                            handleEditClick={handleEditClick}
                            handleUpdate={handleUpdate}
                            handleDelete={handleDelete}
                            handleCancelEdit={handleCancelEdit}
                            handleInputChange={handleInputChange}
                        />
                    </div>

                    <div className="mt-6 flex justify-start">
                        <Button
                            as="link"
                            variant="outline"
                            href={route("data-siswa.index")}
                        >
                            <ArrowLeft size={16} className="mr-2" />
                            Kembali
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    );
};

ShowClass.layout = (page) => (
    <MainLayout
        children={page}
        title={`Siswa Kelas ${page.props.selectedClass.kelas}`}
    />
);

export default ShowClass;
