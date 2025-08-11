import { useEffect } from "react";

import toast from "react-hot-toast";
import { usePage } from "@inertiajs/react";
import { Save, Users, PlusCircle, Upload, ArrowLeft } from "lucide-react";

// Components
import MainLayout from "@/Layouts/MainLayout";
import Button from "@/Components/common/button";
import Select from "@/Components/common/select";
import PageContent from "@/Components/ui/page-content";
import InputSiswaCard from "@/Components/data-siswa/input-siswa-card";
import InputSiswaTable from "@/Components/data-siswa/input-siswa-table";
import useInputSiswaForm from "@/hooks/data-siswa/use-input-siswa";

const InputData = () => {
    const {
        data,
        isSubmitting,
        errors,
        formRef,
        fileInputRef,
        importError,
        allJurusans,
        isLoadingJurusans,
        kelasOptions,
        isLoadingKelas,
        handleFormChange,
        handleStudentChange,
        addStudentRow,
        removeStudentRow,
        handleSubmit,
        handleCreateJurusan,
        handleDeleteJurusan,
        handleCreateKelas,
        handleDeleteKelas,
        handleImportClick,
        handleFileChange,
    } = useInputSiswaForm();

    const { props } = usePage();

    useEffect(() => {
        if (props.flash?.success) toast.success(props.flash.success);
        if (props.flash?.error) toast.error(props.flash.error);
    }, [props.flash]);

    const breadcrumbItems = [
        { label: "Data Siswa", href: route("data-siswa.index") },
        { label: "Input Data Baru", href: null },
    ];

    const jurusanOptions =
        allJurusans?.map((j) => ({ value: j.id, label: j.nama_jurusan })) || [];
    const kelasSelectOptions =
        kelasOptions?.map((k) => ({
            value: k.id,
            label: `${k.nama_kelas} ${k.kelompok}`,
        })) || [];
    const jurusanSelected =
        allJurusans?.find((j) => j.id === data.jurusan_id)?.nama_jurusan || "";

    return (
        <PageContent breadcrumbItems={breadcrumbItems} pageClassName="-mt-20">
            <div className="flex items-center space-x-3 mb-6">
                <div className="p-3 bg-sky-100 rounded-lg">
                    <Users className="w-5 h-5 md:w-6 md:h-6 text-sky-600 " />
                </div>
                <div>
                    <h3 className="text-md md:text-lg font-medium text-neutral-700">
                        Input Data Kelas & Siswa
                    </h3>
                    <p className="text-xs md:text-sm text-neutral-500">
                        Pilih kelas dan masukkan daftar siswa di bawah ini.
                    </p>
                </div>
            </div>

            <form
                ref={formRef}
                onSubmit={handleSubmit}
                className="space-y-6"
                noValidate
            >
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Select
                        label="Pilih Jurusan"
                        title="Manajemen Jurusan"
                        description="Anda dapat mencari, menambah, atau menghapus jurusan."
                        options={jurusanOptions}
                        value={data.jurusan_id}
                        onChange={(value) =>
                            handleFormChange("jurusan_id", value)
                        }
                        isLoading={isLoadingJurusans}
                        placeholder="-- Pilih Jurusan --"
                        error={errors.jurusan_id?.[0]}
                        allowAdd
                        onAdd={handleCreateJurusan}
                        allowDelete
                        onDelete={handleDeleteJurusan}
                        isProcessing={isSubmitting}
                    />

                    <Select
                        label="Pilih Kelas"
                        title={
                            jurusanSelected
                                ? `Kelas untuk ${jurusanSelected}`
                                : "Pilih Jurusan Dahulu"
                        }
                        description={
                            jurusanSelected
                                ? "Berikut daftar kelas yang tersedia untuk jurusan ini."
                                : "Pilih jurusan di sebelah kiri untuk melihat daftar kelas."
                        }
                        options={kelasSelectOptions}
                        value={data.kelas_id}
                        onChange={(value) =>
                            handleFormChange("kelas_id", value)
                        }
                        isLoading={isLoadingKelas}
                        disabled={!data.jurusan_id}
                        placeholder={
                            !data.jurusan_id
                                ? "Pilih jurusan terlebih dahulu"
                                : "-- Pilih Kelas --"
                        }
                        error={errors.kelas_id?.[0]}
                        allowAdd
                        onAdd={handleCreateKelas}
                        allowDelete
                        onDelete={handleDeleteKelas}
                        isProcessing={isSubmitting}
                    />
                </div>

                <div className="border-t border-neutral-200 pt-6">
                    <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
                        <label className="block text-sm font-medium text-neutral-700">
                            Daftar Siswa <span className="text-red-600">*</span>
                        </label>
                        <div className="flex items-center justify-end space-x-2">
                            <input
                                type="file"
                                ref={fileInputRef}
                                onChange={handleFileChange}
                                style={{ display: "none" }}
                                accept=".csv, .xlsx, .xls"
                            />
                            <button
                                type="button"
                                onClick={handleImportClick}
                                className="flex items-center space-x-2 text-xs font-medium text-sky-600 bg-sky-100 hover:bg-sky-200 p-2 md:px-3 md:py-2 rounded-lg transition-colors cursor-pointer"
                            >
                                <Upload size={16} />
                                <span>Import Data</span>
                            </button>
                            <button
                                type="button"
                                onClick={addStudentRow}
                                className="flex items-center space-x-2 text-xs font-medium text-green-600 bg-green-100 hover:bg-green-200 p-2 md:px-3 md:py-2 rounded-lg transition-colors cursor-pointer"
                            >
                                <PlusCircle size={16} />
                                <span>Tambah Siswa</span>
                            </button>
                        </div>
                    </div>
                    {importError && (
                        <div className="text-center bg-red-50 border border-red-200 text-red-700 text-sm p-3 rounded-lg mb-4 w-fit">
                            {importError}
                        </div>
                    )}
                    {errors.students && (
                        <div
                            id="students-list-error"
                            className="text-center bg-red-50 border border-red-200 text-red-700 text-sm p-3 rounded-lg mb-4 w-fit"
                        >
                            {errors.students[0] || errors.students}
                        </div>
                    )}

                    <div className="hidden lg:block">
                        <InputSiswaTable
                            students={data.students}
                            handleStudentChange={handleStudentChange}
                            removeStudentRow={removeStudentRow}
                            displayErrors={errors}
                        />
                    </div>
                    <div className="lg:hidden">
                        <InputSiswaCard
                            students={data.students}
                            handleStudentChange={handleStudentChange}
                            removeStudentRow={removeStudentRow}
                            displayErrors={errors}
                        />
                    </div>
                </div>

                <div className="mt-6 flex items-center justify-end space-x-4">
                    <Button
                        as="link"
                        variant="outline"
                        href={route("data-siswa.index")}
                    >
                        <ArrowLeft size={16} className="mr-2" />
                        Kembali
                    </Button>
                    <Button
                        type="submit"
                        variant="primary"
                        disabled={isSubmitting}
                    >
                        <Save className="w-4 h-4 mr-2" />
                        {isSubmitting ? "Menyimpan..." : "Simpan"}
                    </Button>
                </div>
            </form>
        </PageContent>
    );
};

InputData.layout = (page) => (
    <MainLayout children={page} title="Input Data Siswa" />
);

export default InputData;
