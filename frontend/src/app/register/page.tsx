import type { Metadata } from "next"
import RegisterForm from "@/components/auth/RegisterForm"

export const metadata: Metadata = { title: "Inscription" }

export default function RegisterPage() {
	return (
		<div className="flex min-h-screen items-center justify-center">
			<RegisterForm />
		</div>
	)
}
