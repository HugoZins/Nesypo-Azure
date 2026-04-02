import { useMutation } from "@tanstack/react-query"
import { toast } from "sonner"
import { authApi } from "@/lib/authApi"
import { useAuthStore } from "@/stores/useAuthStore"

export function useLogout() {
	const { logout } = useAuthStore()

	return useMutation({
		mutationFn: authApi.logout,
		onSuccess: () => {
			logout()
		},
		onError: () => {
			toast.error("Erreur lors de la déconnexion")
			logout()
		},
	})
}