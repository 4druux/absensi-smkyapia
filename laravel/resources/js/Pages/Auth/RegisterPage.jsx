import { useEffect, useState } from "react";
import { Head, Link, useForm, usePage } from "@inertiajs/react";
import ButtonRounded from "@/Components/common/button-rounded";
import { Loader2, Eye, EyeOff, AlertCircle } from "lucide-react";
import toast from "react-hot-toast";
import Select from "@/Components/common/select";

const RegisterPage = ({ registeredRoles = [] }) => {
    const { props } = usePage();

    const { data, setData, post, processing, errors, reset } = useForm({
        name: "",
        email: "",
        password: "",
        password_confirmation: "",
        role: "",
        _token: props.csrf_token,
    });

    const [showPassword, setShowPassword] = useState(false);
    const [showPasswordConfirmation, setShowPasswordConfirmation] =
        useState(false);

    useEffect(() => {
        if (errors.name) {
            toast.error(errors.name);
        } else if (errors.email) {
            toast.error(errors.email);
        } else if (errors.password) {
            toast.error(errors.password);
        } else if (errors.password_confirmation) {
            toast.error(errors.password_confirmation);
        } else if (errors.role) {
            toast.error(errors.role);
        }
    }, [errors]);

    useEffect(() => {
        return () => {
            reset("password", "password_confirmation");
        };
    }, [reset]);

    const submit = (e) => {
        e.preventDefault();
        post(route("register"));
    };

    const roles = [
        { value: "Guru", label: "Guru" },
        { value: "Wali Kelas", label: "Wali Kelas" },
        { value: "Bendahara kelas", label: "Bendahara kelas" },
        { value: "Admin", label: "Admin" },
        { value: "Super Admin", label: "Super Admin" },
    ];

    const availableRoles = roles.filter(
        (role) =>
            role.value !== "Super Admin" ||
            !registeredRoles.includes("Super Admin")
    );

    return (
        <div className="flex min-h-screen items-center justify-center bg-gray-50 px-4">
            <Head title="Register" />
            <div className="w-full max-w-md space-y-6">
                <div className="rounded-2xl bg-white p-8 shadow-md border border-slate-100">
                    <h3 className="text-center text-2xl font-medium text-sky-500 mb-10">
                        Buat Akun Baru
                    </h3>
                    <form onSubmit={submit} className="space-y-6">
                        {Object.keys(errors).length > 0 && (
                            <div className="p-3 text-sm text-red-700 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                                <AlertCircle className="w-5 h-5 mr-2" />
                                <span>{Object.values(errors)[0]}</span>
                            </div>
                        )}
                        <div>
                            <label
                                htmlFor="name"
                                className="block text-sm font-medium text-neutral-700"
                            >
                                Nama Lengkap
                            </label>
                            <div className="mt-1">
                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    autoComplete="name"
                                    value={data.name}
                                    onChange={(e) =>
                                        setData("name", e.target.value)
                                    }
                                    required
                                    className={`w-full px-4 py-2 mt-2 border rounded-lg focus:outline-none placeholder:text-neutral-400 placeholder:text-sm ${
                                        errors.name
                                            ? "border-red-500"
                                            : "border-slate-300 focus:border-sky-500"
                                    }`}
                                    placeholder="Nama Anda"
                                />
                            </div>
                        </div>
                        <div>
                            <label
                                htmlFor="email"
                                className="block text-sm font-medium text-neutral-700"
                            >
                                Email
                            </label>
                            <div className="mt-1">
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    autoComplete="username"
                                    value={data.email}
                                    onChange={(e) =>
                                        setData("email", e.target.value)
                                    }
                                    required
                                    className={`w-full px-4 py-2 mt-2 border rounded-lg focus:outline-none placeholder:text-neutral-400 placeholder:text-sm ${
                                        errors.email
                                            ? "border-red-500"
                                            : "border-slate-300 focus:border-sky-500"
                                    }`}
                                    placeholder="Email Anda"
                                />
                            </div>
                        </div>
                        <div className="relative">
                            <label
                                htmlFor="password"
                                className="block text-sm font-medium text-neutral-700"
                            >
                                Password
                            </label>
                            <div className="mt-1">
                                <input
                                    id="password"
                                    name="password"
                                    type={showPassword ? "text" : "password"}
                                    autoComplete="new-password"
                                    value={data.password}
                                    onChange={(e) =>
                                        setData("password", e.target.value)
                                    }
                                    required
                                    className={`w-full px-4 py-2 mt-2 border rounded-lg focus:outline-none placeholder:text-neutral-400 placeholder:text-sm ${
                                        errors.password
                                            ? "border-red-500"
                                            : "border-slate-300 focus:border-sky-500"
                                    }`}
                                    placeholder="Password Anda"
                                />
                                <button
                                    type="button"
                                    onClick={() =>
                                        setShowPassword(!showPassword)
                                    }
                                    className="absolute inset-y-0 right-0 top-6 flex items-center px-3 text-neutral-500 cursor-pointer"
                                >
                                    {showPassword ? (
                                        <EyeOff size={20} />
                                    ) : (
                                        <Eye size={20} />
                                    )}
                                </button>
                            </div>
                        </div>
                        <div className="relative">
                            <label
                                htmlFor="password_confirmation"
                                className="block text-sm font-medium text-neutral-700"
                            >
                                Konfirmasi Password
                            </label>
                            <div className="mt-1">
                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type={
                                        showPasswordConfirmation
                                            ? "text"
                                            : "password"
                                    }
                                    autoComplete="new-password"
                                    value={data.password_confirmation}
                                    onChange={(e) =>
                                        setData(
                                            "password_confirmation",
                                            e.target.value
                                        )
                                    }
                                    required
                                    className={`w-full px-4 py-2 mt-2 border rounded-lg focus:outline-none placeholder:text-neutral-400 placeholder:text-sm ${
                                        errors.password_confirmation
                                            ? "border-red-500"
                                            : "border-slate-300 focus:border-sky-500"
                                    }`}
                                    placeholder="Konfirmasi Password Anda"
                                />
                                <button
                                    type="button"
                                    onClick={() =>
                                        setShowPasswordConfirmation(
                                            !showPasswordConfirmation
                                        )
                                    }
                                    className="absolute inset-y-0 right-0 top-6 flex items-center px-3 text-neutral-500 cursor-pointer"
                                >
                                    {showPasswordConfirmation ? (
                                        <EyeOff size={20} />
                                    ) : (
                                        <Eye size={20} />
                                    )}
                                </button>
                            </div>
                        </div>

                        <Select
                            label="Role (Pilih Peran Anda)"
                            options={availableRoles}
                            value={data.role}
                            onChange={(value) => setData("role", value)}
                            error={errors.role}
                            isSearchable={false}
                        />

                        <div className="flex justify-center pt-2">
                            <ButtonRounded
                                type="submit"
                                className="w-full py-3"
                                disabled={processing}
                            >
                                {processing ? (
                                    <>
                                        <Loader2 className="animate-spin h-5 w-5 mr-3" />
                                        <span>Memproses...</span>
                                    </>
                                ) : (
                                    "Daftar"
                                )}
                            </ButtonRounded>
                        </div>
                    </form>
                    <div className="mt-4 text-center">
                        <p className="text-sm text-neutral-600">
                            Sudah punya akun?{" "}
                            <Link
                                href={route("login")}
                                className="font-medium text-sky-600 hover:underline"
                            >
                                Login sekarang
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
};

RegisterPage.layout = null;

export default RegisterPage;
