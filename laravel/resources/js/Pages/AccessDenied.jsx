import { Head } from "@inertiajs/react";
import ButtonRounded from "@/Components/common/button-rounded";
import PageContent from "@/Components/ui/page-content";
import { ArrowLeft } from "lucide-react";

export default function AccessDenied() {
    const breadcrumbItems = [{ label: "Akses Ditolak", href: null }];

    return (
        <PageContent breadcrumbItems={breadcrumbItems} pageClassName="-mt-20">
            <Head title="Akses Ditolak" />
            <div className="flex flex-col items-center justify-center py-24">
                <h1 className="text-2xl font-extrabold text-sky-500">
                    Oppsss! Akses Ditolak
                </h1>

                <p className="mt-2 text-md font-medium text-neutral-500">
                    Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.
                </p>

                <p className="text-sm mt-2 text-neutral-500">
                    Halaman
                    <span className="text-neutral-600 px-2 tracking-wider font-semibold">
                        &ldquo;{location.pathname}&rdquo;
                    </span>
                    yang Anda cari sepertinya tidak tersedia. Tapi tenang, Anda
                    selalu bisa kembali ke beranda untuk melanjutkan.
                </p>
            </div>

            <div className="flex justify-start mt-8">
                <ButtonRounded as="link" variant="outline" href={route("home")}>
                    <ArrowLeft size={16} className="mr-2" />
                    Kembali
                </ButtonRounded>
            </div>
        </PageContent>
    );
}
