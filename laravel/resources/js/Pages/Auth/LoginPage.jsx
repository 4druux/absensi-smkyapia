import React, { useEffect, useState } from "react";
import { Head, Link, useForm, usePage } from "@inertiajs/react";
import ButtonRounded from "@/Components/common/button-rounded";
import { Loader2, Eye, EyeOff } from "lucide-react";
import toast from "react-hot-toast";

const LoginPage = () => {
    const { flash } = usePage().props;

    const { data, setData, post, processing, errors } = useForm({
        email: "",
        password: "",
    });

    const [showPassword, setShowPassword] = useState(false);

    useEffect(() => {
        if (flash.success) {
            toast.success(flash.success);
        }
        if (flash.error) {
            toast.error(flash.error);
        }
    }, [flash.success, flash.error]);

    useEffect(() => {
        if (errors.email) {
            toast.error(errors.email);
        } else if (errors.password) {
            toast.error(errors.password);
        }
    }, [errors]);

    const submit = (e) => {
        e.preventDefault();
        post(route("login"));
    };

    return (
        <div className="flex min-h-screen items-center justify-center bg-gray-50 px-4">
            <Head title="Login" />
            <div className="w-full max-w-md space-y-6">
                <div className="rounded-2xl bg-white p-8 shadow-md border border-slate-100">
                    <h3 className="text-center text-2xl font-medium text-sky-500 mb-10">
                        Login
                    </h3>

                    <form onSubmit={submit} className="space-y-6">
                        <div>
                            <label
                                htmlFor="email"
                                className="block text-sm font-medium text-gray-700"
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
                                    className={`w-full px-4 py-2 mt-2 border rounded-lg focus:outline-none placeholder:text-gray-400 placeholder:text-sm ${
                                        errors.email
                                            ? "border-red-500"
                                            : "border-gray-300 focus:border-sky-500"
                                    }`}
                                    placeholder="Email Anda"
                                />
                            </div>
                        </div>
                        <div className="relative">
                            <label
                                htmlFor="password"
                                className="block text-sm font-medium text-gray-700"
                            >
                                Password
                            </label>
                            <div className="mt-1">
                                <input
                                    id="password"
                                    name="password"
                                    type={showPassword ? "text" : "password"}
                                    autoComplete="current-password"
                                    value={data.password}
                                    onChange={(e) =>
                                        setData("password", e.target.value)
                                    }
                                    required
                                    className={`w-full px-4 py-2 mt-2 border rounded-lg focus:outline-none placeholder:text-gray-400 placeholder:text-sm ${
                                        errors.password
                                            ? "border-red-500"
                                            : "border-gray-300 focus:border-sky-500"
                                    }`}
                                    placeholder="Password Anda"
                                />
                                <button
                                    type="button"
                                    onClick={() =>
                                        setShowPassword(!showPassword)
                                    }
                                    className="absolute inset-y-0 right-0 top-6 flex items-center px-3 text-gray-500 cursor-pointer"
                                >
                                    {showPassword ? (
                                        <EyeOff size={20} />
                                    ) : (
                                        <Eye size={20} />
                                    )}
                                </button>
                            </div>
                        </div>
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
                                    "Masuk"
                                )}
                            </ButtonRounded>
                        </div>
                    </form>
                    <div className="mt-4 text-center">
                        <p className="text-sm text-gray-600">
                            Belum punya akun?{" "}
                            <Link
                                href={route("register")}
                                className="font-medium text-sky-600 hover:underline"
                            >
                                Daftar sekarang
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
};

LoginPage.layout = null;

export default LoginPage;
