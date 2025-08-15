import { ArrowLeft, BookOpenText } from "lucide-react";
import { usePage } from "@inertiajs/react";
import toast from "react-hot-toast";
import { useEffect, useState } from "react";

import ButtonRounded from "@/Components/common/button-rounded";
import DataNotFound from "@/Components/ui/data-not-found";
import DotLoader from "@/Components/ui/dot-loader";
import PageContent from "@/Components/ui/page-content";
import ShowRekapTable from "@/Components/rekapitulasi/show-rekap-table";
import ShowRekapCard from "@/Components/rekapitulasi/show-rekap-card";
import { useSiswaNotes } from "@/hooks/rekapitulasi/use-siswa-notes";

const ShowMonthPage = ({
    students: initialStudents,
    selectedClass,
    tahun,
    bulanSlug,
}) => {
    const { flash } = usePage().props;
    const [students, setStudents] = useState([]);
    const [isSaving, setIsSaving] = useState({});
    const { handleStoreNote } = useSiswaNotes(tahun, bulanSlug);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        if (flash && flash.error) {
            toast.error(flash.error);
        }

        if (initialStudents) {
            const studentsWithEditableFields = initialStudents.map(
                (student) => ({
                    ...student,
                    poin_tambahan: student.poin_tambahan || "",
                    keterangan: student.keterangan || "",
                })
            );
            setStudents(studentsWithEditableFields);
            setIsLoading(false);
        }
    }, [flash, initialStudents]);

    const handleInputChange = (e, index) => {
        const { name, value } = e.target;
        const newStudents = [...students];

        if (name === "poin_tambahan") {
            newStudents[index][name] = value.replace(",", ".");
        } else {
            newStudents[index][name] = value;
        }
        setStudents(newStudents);
    };

    const handleSave = async (studentToSave) => {
        const key = studentToSave.id;
        setIsSaving((prev) => ({ ...prev, [key]: true }));

        const poin = parseFloat(studentToSave.poin_tambahan) || 0;

        await handleStoreNote(studentToSave.id, poin, studentToSave.keterangan);

        setIsSaving((prev) => ({ ...prev, [key]: false }));
    };

    const fullClassName = `${selectedClass.kelas} ${selectedClass.kelompok} - ${selectedClass.jurusan}`;

    const breadcrumbItems = [
        { label: "Rekapitulasi", href: route("rekapitulasi.index") },
        {
            label: fullClassName,
            href: route("rekapitulasi.class.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
            }),
        },
        {
            label: tahun,
            href: route("rekapitulasi.year.show", {
                kelas: selectedClass.kelas,
                jurusan: selectedClass.jurusan,
                tahun: tahun,
            }),
        },
        { label: bulanSlug, href: null },
    ];

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-screen">
                <DotLoader text="Memuat data rekapitulasi..." />
            </div>
        );
    }

    if (!students || students.length === 0) {
        return (
            <PageContent
                breadcrumbItems={breadcrumbItems}
                pageClassName="-mt-20"
            >
                <DataNotFound
                    title="Data Rekapitulasi Kosong"
                    message={`Tidak ada data rekapitulasi untuk bulan ${bulanSlug} tahun ajaran ${tahun}.`}
                />
            </PageContent>
        );
    }

    const tableProps = {
        students,
        isSaving,
        handleInputChange,
        handleSave,
    };

    return (
        <PageContent breadcrumbItems={breadcrumbItems} pageClassName="-mt-16 md:-mt-20">
            <div className="flex items-center space-x-2 md:space-x-3 mb-6">
                <div className="p-3 bg-sky-100 rounded-lg">
                    <BookOpenText className="w-5 h-5 md:w-6 md:h-6 text-sky-600 " />
                </div>
                <div>
                    <h3 className="text-md md:text-lg font-medium text-neutral-700">
                        Pencatatan Poin dan Keterangan {bulanSlug}
                    </h3>
                    <p className="text-xs md:text-sm text-neutral-500">
                        {fullClassName} - Tahun Ajaran {tahun}
                    </p>
                </div>
            </div>
            {students.length > 0 ? (
                <>
                    <div className="hidden lg:block">
                        <ShowRekapTable {...tableProps} />
                    </div>
                    <div className="lg:hidden">
                        <ShowRekapCard {...tableProps} />
                    </div>
                </>
            ) : (
                <DataNotFound
                    title="Data Siswa Kosong"
                    message={`Tidak ada data siswa yang ditemukan untuk kelas ini.`}
                />
            )}
            <div className="mt-6 flex justify-start">
                <ButtonRounded
                    as="link"
                    variant="outline"
                    href={route("rekapitulasi.year.show", {
                        kelas: selectedClass.kelas,
                        jurusan: selectedClass.jurusan,
                        tahun: tahun,
                    })}
                >
                    <ArrowLeft size={16} className="mr-2" />
                    Kembali
                </ButtonRounded>
            </div>
        </PageContent>
    );
};

export default ShowMonthPage;
