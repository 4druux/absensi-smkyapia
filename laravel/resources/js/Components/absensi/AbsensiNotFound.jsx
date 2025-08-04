import { AlertTriangle } from "lucide-react";
import BreadcrumbNav from "../common/BreadcrumbNav";

const AbsensiNotFound = ({ breadcrumbItems, title, message }) => {
    return (
        <div>
            <BreadcrumbNav items={breadcrumbItems} />
            <div className="px-3 -mt-20 md:px-7 pb-10">
                <div className="flex flex-col items-center justify-center p-4 py-20 space-y-2 bg-white rounded-2xl shadow-lg">
                    <AlertTriangle className="w-12 h-12 mx-auto text-yellow-400" />
                    <h3 className="mt-2 text-lg font-medium text-neutral-700">
                        {title}
                    </h3>
                    <p className="px-4 text-sm text-center text-neutral-500">
                        {message}
                    </p>
                </div>
            </div>
        </div>
    );
};

export default AbsensiNotFound;
