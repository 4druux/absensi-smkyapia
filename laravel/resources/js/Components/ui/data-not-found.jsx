import { AlertTriangle } from "lucide-react";

const DataNotFound = ({ title, message }) => {
    return (
        <div className="flex flex-col items-center justify-center p-4 py-20 space-y-2">
            <AlertTriangle className="w-12 h-12 mx-auto text-yellow-400" />
            <h3 className="mt-2 text-lg font-medium text-neutral-700">
                {title}
            </h3>
            <p className="px-4 text-sm text-center text-neutral-500">
                {message}
            </p>
        </div>
    );
};

export default DataNotFound;
