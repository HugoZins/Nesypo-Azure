"use client"

import { zodResolver } from "@hookform/resolvers/zod"
import { useRouter } from "next/navigation"
import { useState } from "react"
import { Controller, useForm } from "react-hook-form"
import { toast } from "sonner"
import type { z } from "zod"
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { api } from "@/lib/api"
import { authApi } from "@/lib/authApi"
import type { Me } from "@/types/todo"
import { registerSchema } from "@/lib/validation/auth"
import { useAuthStore } from "@/stores/useAuthStore"

type RegisterFormValues = z.infer<typeof registerSchema>

export default function RegisterForm() {
	const router = useRouter()
	const setAuth = useAuthStore((s) => s.setAuth)
	const [errorMessage, setErrorMessage] = useState<string | null>(null)
	
	const form = useForm<RegisterFormValues>({
		resolver: zodResolver(registerSchema),
		defaultValues: { email: "", password: "", passwordConfirm: "" },
	})
	
	const onSubmit = async (data: RegisterFormValues) => {
		setErrorMessage(null)
		
		try {
			await authApi.register(data.email, data.password, data.passwordConfirm)
			await authApi.login(data.email, data.password)
			const me = await api.get("api/me").json<Me>()
			setAuth(me)
			toast.success("Compte créé avec succès")
			router.push("/dashboard")
		} catch (e: unknown) {
			const error = e as {
				response?: { data?: { message?: string } }
				message?: string
			}
			const message = error?.response?.data?.message ?? error?.message ?? "Erreur lors de l'inscription"
			setErrorMessage(message)
		}
	}
	
	return (
		<Card className="w-[380px]">
		<CardHeader>
		<CardTitle>Créer un compte</CardTitle>
		</CardHeader>
		
		<CardContent>
		{errorMessage && (
			<Alert variant="destructive" className="mb-4">
			<AlertTitle>Erreur</AlertTitle>
			<AlertDescription>{errorMessage}</AlertDescription>
			</Alert>
		)}
		
		<form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
		<Controller
		name="email"
		control={form.control}
		render={({ field, fieldState }) => (
			<div className="space-y-1">
			<Label>Email</Label>
			<Input
			{...field}
			id="email"
			type="email"
			placeholder="ex: toto@mail.com"
			aria-invalid={fieldState.invalid}
			/>
			{fieldState.error && <p className="text-destructive text-sm">{fieldState.error.message}</p>}
			</div>
		)}
		/>
		
		<Controller
		name="password"
		control={form.control}
		render={({ field, fieldState }) => (
			<div className="space-y-1">
			<Label>Mot de passe</Label>
			<Input
			{...field}
			id="password"
			type="password"
			placeholder="••••••••"
			aria-invalid={fieldState.invalid}
			/>
			{fieldState.error && <p className="text-destructive text-sm">{fieldState.error.message}</p>}
			</div>
		)}
		/>
		
		<Controller
		name="passwordConfirm"
		control={form.control}
		render={({ field, fieldState }) => (
			<div className="space-y-1">
			<Label>Confirmer le mot de passe</Label>
			<Input
			{...field}
			id="passwordConfirm"
			type="password"
			placeholder="••••••••"
			aria-invalid={fieldState.invalid}
			/>
			{fieldState.error && <p className="text-destructive text-sm">{fieldState.error.message}</p>}
			</div>
		)}
		/>
		
		<Button type="submit" className="w-full" disabled={form.formState.isSubmitting}>
		{form.formState.isSubmitting ? "Inscription..." : "S'inscrire"}
		</Button>
		
		<div className="text-center text-muted-foreground text-sm">
		Déjà un compte ?{" "}
		<a href="/login" className="font-medium text-primary hover:underline">
		Se connecter
		</a>
		</div>
		</form>
		</CardContent>
		</Card>
	)
}
