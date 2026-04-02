import { useMutation } from "@tanstack/react-query"
import { authApi } from "@/lib/authApi"

export function useRegister() {
	return useMutation({
		mutationFn: ({ email, password, passwordConfirm }: {
			email: string
			password: string
			passwordConfirm: string
		}) => authApi.register(email, password, passwordConfirm),
	})
}