import { useMutation } from "@tanstack/react-query"
import { authApi } from "@/lib/authApi"

export function useLogin() {
	return useMutation({
		mutationFn: ({ email, password }: { email: string; password: string }) =>
			authApi.login(email, password),
	})
}