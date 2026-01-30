import { api } from "@/lib/api"

export const authApi = {
	login: (email: string, password: string) => api.post("api/login", { json: { email, password } }).json(),

	register: (email: string, password: string, passwordConfirm: string) =>
		api
			.post("api/register", {
				json: { email, password, passwordConfirm },
			})
			.json(),

	logout: () => api.post("api/logout"),
}
