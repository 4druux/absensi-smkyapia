import React, { useState } from "react";
import MainLayout from "@/Layouts/MainLayout";
import { Link, router } from "@inertiajs/react";
import { ArrowLeft, Edit, Trash2, Save, X } from "lucide-react";
import toast from "react-hot-toast";
import Button from "@/Components/common/Button";
import BreadcrumbNav from "@/Components/common/BreadcrumbNav";

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

        // Ensure editData contains all necessary fields for validation on backend
        const payload = {
            ...editData,
            kelas: selectedClass.kelas, // Ensure kelas and jurusan are sent for update validation
            jurusan: selectedClass.jurusan,
        };

        // Menggunakan objek untuk parameter ID
        router.put(route("data-siswa.student.update", { id: id }), payload, {
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

    const handleDelete = (id) => {
        console.log("Attempting to delete student with ID:", id);
        console.log("Type of ID:", typeof id); // Debugging: Check the type of ID

        if (confirm("Apakah Anda yakin ingin menghapus siswa ini?")) {
            const deleteUrl = route("data-siswa.student.destroy", { id: id });
            console.log("Generated DELETE URL for router.delete:", deleteUrl); 

            router.delete(deleteUrl, {
                onSuccess: () => {
                    toast.success("Siswa berhasil dihapus!");
                },
                onError: (errors) => {
                    toast.error("Gagal menghapus siswa.");
                    console.error("Errors from server during delete:", errors);
                },
            });
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
                    <h3 className="text-xl font-medium text-neutral-700 mb-6">
                        Daftar Siswa Kelas {selectedClass.kelas} -{" "}
                        {selectedClass.jurusan}
                    </h3>

                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="bg-neutral-50">
                                <tr>
                                    <th className="w-16 px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                                        No
                                    </th>
                                    <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                                        Nama Siswa
                                    </th>
                                    <th className="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                                        Nomor Induk Siswa
                                    </th>
                                    <th className="px-6 py-3 text-xs font-medium tracking-wider text-center uppercase text-neutral-500">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-neutral-200">
                                {students.map((student, index) => (
                                    <tr
                                        key={student.id}
                                        className="even:bg-neutral-50 hover:bg-neutral-100"
                                    >
                                        <td className="px-6 py-4 text-sm font-medium whitespace-nowrap text-neutral-800">
                                            {index + 1}.
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {editingId === student.id ? (
                                                <input
                                                    type="text"
                                                    name="nama"
                                                    value={editData.nama}
                                                    onChange={handleInputChange}
                                                    className="w-full p-2 border rounded-md"
                                                />
                                            ) : (
                                                <div className="text-sm font-medium text-neutral-800">
                                                    {student.nama}
                                                </div>
                                            )}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {editingId === student.id ? (
                                                <input
                                                    type="text"
                                                    name="nis"
                                                    value={editData.nis}
                                                    onChange={handleInputChange}
                                                    className="w-full p-2 border rounded-md"
                                                />
                                            ) : (
                                                <div className="text-sm font-medium text-neutral-800">
                                                    {student.nis}
                                                </div>
                                            )}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-center">
                                            {editingId === student.id ? (
                                                <div className="flex items-center justify-center space-x-2">
                                                    <Button
                                                        size="sm"
                                                        onClick={(e) =>
                                                            handleUpdate(
                                                                e,
                                                                student.id
                                                            )
                                                        }
                                                    >
                                                        <Save size={16} />
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant="ghost"
                                                        onClick={
                                                            handleCancelEdit
                                                        }
                                                    >
                                                        <X size={16} />
                                                    </Button>
                                                </div>
                                            ) : (
                                                <div className="flex items-center justify-center space-x-2">
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() =>
                                                            handleEditClick(
                                                                student
                                                            )
                                                        }
                                                    >
                                                        <Edit size={16} />
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant="ghost"
                                                        onClick={() =>
                                                            handleDelete(
                                                                student.id
                                                            )
                                                        }
                                                    >
                                                        <Trash2 size={16} />
                                                    </Button>
                                                </div>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="mt-6 flex justify-start">
                        <Button
                            as="link"
                            variant="outline"
                            href={route("data-siswa.index")}
                        >
                            <ArrowLeft size={16} className="mr-2" />
                            Kembali ke Daftar Kelas
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
