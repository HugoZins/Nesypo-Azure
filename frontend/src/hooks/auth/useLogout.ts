import { useMutation } from "@tanstack/react-query"
import { authApi } from "@/lib/authApi"
import { useAuthStore } from "@/stores/useAuthStore"

export function useLogout() {
	const logoutStore = useAuthStore()

	return useMutation({
		mutationFn: authApi.logout,
		onSuccess: () => {
			logoutStore.logout()
			window.location.href = "/login"
		},
	})
}
