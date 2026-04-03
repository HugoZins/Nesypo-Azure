"use client"

import { zodResolver } from "@hookform/resolvers/zod"
import { useRouter } from "next/navigation"
import { Controller, useForm } from "react-hook-form"
import { toast } from "sonner"
import type { z } from "zod"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { api } from "@/lib/api"
import { authApi } from "@/lib/authApi"
import { loginSchema } from "@/lib/validation/auth"
import { useAuthStore } from "@/stores/useAuthStore"
import type { Me } from "@/types/todo"

type LoginFormValues = z.infer<typeof loginSchema>

export default function LoginForm() {
	const router = useRouter()
	const setAuth = useAuthStore((s) => s.setAuth)

	const form = useForm<LoginFormValues>({
		resolver: zodResolver(loginSchema),
		defaultValues: { email: "", password: "" },
	})

	const onSubmit = async (data: LoginFormValues) => {
		try {
			await authApi.login(data.email, data.password)
			const me = await api.get("api/me").json<Me>()
			setAuth(me)
			router.push("/dashboard")
		} catch {
			toast.error("Identifiants invalides")
		}
	}

	return (
		<Card className="mx-auto mt-20 max-w-md">
			<CardHeader>
				<CardTitle>Connexion</CardTitle>
			</CardHeader>

			<CardContent>
				<form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
					<Controller
						name="email"
						control={form.control}
						render={({ field, fieldState }) => (
							<div className="space-y-1">
								<Label htmlFor="email">Email</Label>
								<Input {...field} id="email" type="email" aria-invalid={fieldState.invalid} />
								{fieldState.error && <p className="text-destructive text-sm">{fieldState.error.message}</p>}
							</div>
						)}
					/>

					<Controller
						name="password"
						control={form.control}
						render={({ field, fieldState }) => (
							<div className="space-y-1">
								<Label htmlFor="password">Mot de passe</Label>
								<Input {...field} id="password" type="password" aria-invalid={fieldState.invalid} />
								{fieldState.error && <p className="text-destructive text-sm">{fieldState.error.message}</p>}
							</div>
						)}
					/>

					<Button className="w-full" disabled={form.formState.isSubmitting}>
						{form.formState.isSubmitting ? "Connexion..." : "Se connecter"}
					</Button>

					<div className="text-center text-muted-foreground text-sm">
						Pas encore de compte ?{" "}
						<a href="/register" className="font-medium text-primary hover:underline">
							Inscrivez-vous
						</a>
					</div>
				</form>
			</CardContent>
		</Card>
	)
}
