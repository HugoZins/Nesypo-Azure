import { useMutation } from "@tanstack/react-query"
import { useRouter } from "next/navigation"
import { api } from "@/lib/api"
import { authApi } from "@/lib/authApi"
import { useAuthStore } from "@/stores/useAuthStore"
import type { Me } from "@/types/todo"

export function useLogin() {
	const router = useRouter()
	const setAuth = useAuthStore((s) => s.setAuth)

	return useMutation({
		mutationFn: ({ email, password }: { email: string; password: string }) => authApi.login(email, password),
		onSuccess: async () => {
			// Récupérer les infos utilisateur et les stocker dans Zustand
			const me = await api.get("api/me").json<Me>()
			setAuth(me)
			router.push("/dashboard")
		},
	})
}
