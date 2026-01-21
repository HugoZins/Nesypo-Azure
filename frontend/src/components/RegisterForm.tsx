"use client";

import {useState} from "react";
import {useForm, Controller} from "react-hook-form";
import {zodResolver} from "@hookform/resolvers/zod";
import {registerSchema} from "@/lib/validation/auth";
import {z} from "zod";
import {register} from "@/lib/auth.api";

import {Card, CardContent, CardHeader, CardTitle} from "@/components/ui/card";
import {Label} from "@/components/ui/label";
import {Input} from "@/components/ui/input";
import {Button} from "@/components/ui/button";
import {Alert, AlertDescription, AlertTitle} from "@/components/ui/alert";

type RegisterFormValues = z.infer<typeof registerSchema>;

export default function RegisterForm() {
    const [errorMessage, setErrorMessage] = useState<string | null>(null);

    const form = useForm<RegisterFormValues>({
        resolver: zodResolver(registerSchema),
        defaultValues: {
            email: "",
            password: "",
            passwordConfirm: "",
        },
    });

    const onSubmit = async (data: RegisterFormValues) => {
        setErrorMessage(null);

        try {
            await register(data.email, data.password, data.passwordConfirm);
            alert("Inscription réussie !");
        } catch (e: any) {
            setErrorMessage(e?.message ?? "Erreur inconnue");
        }
    };

    return (
        <Card className="w-[380px]">
            <CardHeader>
                <CardTitle>Créer un compte</CardTitle>
            </CardHeader>

            <CardContent>
                {errorMessage && (
                    <Alert className="mb-4">
                        <AlertTitle>Erreur</AlertTitle>
                        <AlertDescription>{errorMessage}</AlertDescription>
                    </Alert>
                )}

                <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
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
                                    placeholder="ex: toto@mail.com"
                                    aria-invalid={fieldState.invalid}
                                />
                                {fieldState.error && (
                                    <p className="text-sm text-destructive">
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
                                    placeholder="••••••••"
                                    aria-invalid={fieldState.invalid}
                                />
                                {fieldState.error && (
                                    <p className="text-sm text-destructive">
                                        {fieldState.error.message}
                                    </p>
                                )}
                            </div>
                        )}
                    />

                    {/* PASSWORD CONFIRM */}
                    <Controller
                        name="passwordConfirm"
                        control={form.control}
                        render={({field, fieldState}) => (
                            <div className="space-y-1">
                                <Label>Confirmer le mot de passe</Label>
                                <Input
                                    {...field}
                                    id="passwordConfirm"
                                    type="password"
                                    placeholder="••••••••"
                                    aria-invalid={fieldState.invalid}
                                />
                                {fieldState.error && (
                                    <p className="text-sm text-destructive">
                                        {fieldState.error.message}
                                    </p>
                                )}
                            </div>
                        )}
                    />

                    <Button type="submit" className="w-full" disabled={form.formState.isSubmitting}>
                        {form.formState.isSubmitting ? "Inscription..." : "S’inscrire"}
                    </Button>
                </form>
            </CardContent>
        </Card>
    );
}
