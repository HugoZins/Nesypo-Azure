import { useMutation } from "@tanstack/react-query"
import { authApi } from "@/lib/authApi"
import { useAuthStore } from "@/stores/useAuthStore"

export function useLogout() {
	const logout = useAuthStore((s) => s.logout)

	return useMutation({
		mutationFn: () => authApi.logout(),
		onSuccess: () => logout(),
		onError: () => logout(),
	})
}
