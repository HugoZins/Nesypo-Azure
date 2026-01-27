"use client";

import {useForm, Controller} from "react-hook-form";
import {zodResolver} from "@hookform/resolvers/zod";
import {loginSchema} from "@/lib/validation/auth";
import {authApi} from "@/lib/authApi";
import {useRouter} from "next/navigation";
import {z} from "zod";

import {Button} from "@/components/ui/button";
import {Input} from "@/components/ui/input";
import {Card, CardContent, CardHeader, CardTitle,} from "@/components/ui/card";
import {Label} from "@/components/ui/label";

type LoginFormValues = z.infer<typeof loginSchema>;

export default function LoginForm() {
    const router = useRouter();

    const form = useForm<LoginFormValues>({
        resolver: zodResolver(loginSchema),
        defaultValues: {
            email: "",
            password: "",
        },
    });

    const onSubmit = async (data: LoginFormValues) => {
        try {
            await authApi.login(data.email, data.password);
            router.push("/dashboard");
        } catch {
            alert("Identifiants invalides");
        }
    };

    return (
        <Card className="mx-auto mt-20 max-w-md">
            <CardHeader>
                <CardTitle>Connexion</CardTitle>
            </CardHeader>

            <CardContent>
                <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
                    {/* EMAIL */}
                    <Controller
                        name="email"
                        control={form.control}
                        render={({field, fieldState}) => (
                            <div className="space-y-1">
                                <Label>Email</Label>
                                <Input
                                    {...field}
                                    id="email"
                                    type="email"
                                    aria-invalid={fieldState.invalid}
                                />
                                {fieldState.error && (
                                    <p className="text-sm text-red-500">
                                        {fieldState.error.message}
                                    </p>
                                )}
                            </div>
                        )}
                    />

                    {/* PASSWORD */}
                    <Controller
                        name="password"
                        control={form.control}
                        render={({field, fieldState}) => (
                            <div className="space-y-1">
                                <Label>Mot de passe</Label>
                                <Input
                                    {...field}
                                    id="password"
                                    type="password"
                                    aria-invalid={fieldState.invalid}
                                />
                                {fieldState.error && (
                                    <p className="text-sm text-red-500">
                                        {fieldState.error.message}
                                    </p>
                                )}
                            </div>
                        )}
                    />

                    <Button className="w-full" disabled={form.formState.isSubmitting}>
                        Se connecter
                    </Button>
                    <div className="text-center text-sm text-muted-foreground">
                        Pas encore de compte ?{" "}
                        <a
                            href="/register"
                            className="font-medium text-primary hover:underline"
                        >
                            Inscrivez-vous
                        </a>
                    </div>

                </form>
            </CardContent>
        </Card>
    );
}
