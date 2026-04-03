import { useMutation } from "@tanstack/react-query"
import { useRouter } from "next/navigation"
import { api } from "@/lib/api"
import { authApi } from "@/lib/authApi"
import { useAuthStore } from "@/stores/useAuthStore"
import type { Me } from "@/types/todo"

export function useRegister() {
	const router = useRouter()
	const setAuth = useAuthStore((s) => s.setAuth)

	return useMutation({
		mutationFn: ({ email, password, passwordConfirm }: { email: string; password: string; passwordConfirm: string }) =>
			authApi.register(email, password, passwordConfirm),
		onSuccess: async () => {
			const me = await api.get("api/me").json<Me>()
			setAuth(me)
			router.push("/dashboard")
		},
	})
}
