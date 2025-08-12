import { ArrowLeft, Loader2, Save, Users } from "lucide-react";
import MainLayout from "@/Layouts/MainLayout";
import ButtonRounded from "@/Components/common/button-rounded";
import PageContent from "@/Components/ui/page-content";
import DotLoader from "@/Components/ui/dot-loader";
import { useKenaikanForm } from "@/hooks/kenaikan/use-kenaikan-form";
import Select from "@/Components/common/select";

const ShowStudentFormPage = ({ tahun, selectedClass, student }) => {
    const {
        initialData,
        isLoading,
        errors,
        formData,
        isSubmitting,
        handleFormChange,
        handleSubmit,
    } = useKenaikanForm(student.id, tahun);

    const getFirstName = (fullName) => {
        if (!fullName) return "";
        return fullName.split(" ")[0];
    };

    const breadcrumbItems = [
        {
            label: "Kenaikan",
            href: route("kenaikan-bersyarat.index"),
        },
        {
            label: `${selectedClass.kelas} ${selectedClass.kelompok} - ${selectedClass.jurusan}`,
            href: route("kenaikan-bersyarat.index"),
        },
        {
            label: tahun,
            href: route("kenaikan-bersyarat.class.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun: tahun,
            }),
        },
        {
            label: "Daftar Siswa",
            href: route("kenaikan-bersyarat.year.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun: tahun,
            }),
        },
        { label: getFirstName(student.nama), href: null },
    ];

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-screen">
                <DotLoader text="Memuat data siswa..." />
            </div>
        );
    }

    return (
        <PageContent
            breadcrumbItems={breadcrumbItems}
            pageClassName="-mt-16 md:-mt-20"
        >
            <div className="flex items-center space-x-3 mb-8">
                <div className="p-3 bg-sky-100 rounded-lg">
                    <Users className="w-6 h-6 text-sky-600" />
                </div>
                <div>
                    <h3 className="text-md md:text-lg font-medium text-neutral-700">
                        Input Data Kenaikan Bersyarat
                    </h3>
                    <p className="text-xs md:text-sm text-neutral-500">
                        Silahkan isi form penilaian kenaikan kelas bersyarat
                    </p>
                </div>
            </div>

            <form onSubmit={handleSubmit}>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 mb-8">
                    <div>
                        <label className="block text-sm font-medium text-neutral-700">
                            Nama Siswa
                        </label>
                        <div className="py-2 px-3 bg-slate-100 rounded-xl text-neutral-700 mt-2">
                            {student.nama}
                        </div>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-neutral-700">
                            NIS
                        </label>
                        <div className="py-2 px-3 bg-slate-100 rounded-xl text-neutral-700 mt-2">
                            {student.nis}
                        </div>
                    </div>
                </div>

                <div className="mb-8">
                    <label className="block text-sm font-medium text-neutral-700">
                        Kehadiran Non Alfa
                    </label>

                    <div className="py-2 px-3 bg-slate-100 rounded-xl text-neutral-700 mt-2">
                        {initialData?.kehadiranNonAlfa ?? "..."} Hari
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-6 mb-4">
                    <div>
                        <label className="block text-sm font-medium text-neutral-700">
                            Jumlah Nilai Kurang
                        </label>

                        <input
                            id="jumlah_nilai_kurang"
                            type="text"
                            name="jumlah_nilai_kurang"
                            value={formData.jumlah_nilai_kurang}
                            onChange={(e) => {
                                const numericValue = e.target.value.replace(
                                    /\D/g,
                                    ""
                                );
                                handleFormChange(
                                    "jumlah_nilai_kurang",
                                    numericValue
                                );
                            }}
                            placeholder="Jumlah Nilai Kurang (angka)"
                            inputMode="numeric"
                            pattern="[0-9]*"
                            className={`mt-1 w-full px-3 py-2 rounded-xl border focus:outline-none placeholder:text-sm ${
                                errors.jumlah_nilai_kurang
                                    ? "border-red-500"
                                    : "border-slate-300 focus:border-sky-500"
                            }`}
                        />
                        {errors.jumlah_nilai_kurang && (
                            <p className="text-xs text-red-500 mt-1">
                                {errors.jumlah_nilai_kurang}
                            </p>
                        )}
                    </div>
                    <div>
                        <Select
                            label="Akhlak"
                            title="Akhlak"
                            description="Akhlak siswa ini"
                            options={[
                                { value: "Baik", label: "Baik" },
                                { value: "Kurang", label: "Kurang" },
                            ]}
                            value={formData.akhlak}
                            onChange={(value) =>
                                handleFormChange("akhlak", value)
                            }
                            placeholder="Pilih Akhlak"
                            isSearchable={false}
                            error={errors.akhlak}
                        />
                    </div>
                    <div>
                        <Select
                            label="Rekomendasi Wali Kelas"
                            title="Rekomendasi Wali kelas"
                            description="Rekomendasi dari wali kelas untuk siswa ini"
                            options={[
                                {
                                    value: "Tidak Naik",
                                    label: "Tidak Naik",
                                },
                                { value: "Ragu-ragu", label: "Ragu-ragu" },
                            ]}
                            value={formData.rekomendasi_walas}
                            onChange={(value) =>
                                handleFormChange("rekomendasi_walas", value)
                            }
                            placeholder="Pilih Rekomendasi"
                            isSearchable={false}
                            error={errors.rekomendasi_walas}
                        />
                    </div>
                </div>

                <div>
                    <label className="block text-sm font-medium text-neutral-700">
                        Keputusan Akhir
                    </label>

                    <textarea
                        id="keputusan_akhir"
                        name="keputusan_akhir"
                        value={formData.keputusan_akhir}
                        onChange={(e) =>
                            handleFormChange("keputusan_akhir", e.target.value)
                        }
                        placeholder="Keputusan akhir untuk siswa ini"
                        className={`mt-1 w-full px-3 py-2 rounded-xl border focus:outline-none placeholder:text-sm min-h-[150px] ${
                            errors.keputusan_akhir
                                ? "border-red-500"
                                : "border-slate-300 focus:border-sky-500"
                        }`}
                    />
                    {errors.keputusan_akhir && (
                        <p className="text-xs text-red-500 mt-1">
                            {errors.keputusan_akhir}
                        </p>
                    )}
                </div>

                <div className="mt-6 flex items-center justify-end space-x-4">
                    <ButtonRounded
                        as="link"
                        variant="outline"
                        href={route("kenaikan-bersyarat.year.show", {
                            kelas: selectedClass.kelas,
                            jurusan: selectedClass.jurusan,
                            tahun: tahun,
                        })}
                    >
                        <ArrowLeft size={16} className="mr-2" />
                        Kembali
                    </ButtonRounded>

                    <ButtonRounded
                        type="submit"
                        variant="primary"
                        disabled={isSubmitting}
                    >
                        {isSubmitting && (
                            <Loader2 className="animate-spin w-4 h-4 mr-2" />
                        )}
                        <Save className="w-4 h-4 mr-2" />
                        Simpan
                    </ButtonRounded>
                </div>
            </form>
        </PageContent>
    );
};

ShowStudentFormPage.layout = (page) => (
    <MainLayout
        children={page}
        title={`Form Kenaikan - ${page.props.student.nama}`}
    />
);

export default ShowStudentFormPage;
