import ky from "ky"
import { useAuthStore } from "@/stores/useAuthStore"

let isRefreshing = false

export const api = ky.create({
	prefixUrl: process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000",
	credentials: "include",
	hooks: {
		afterResponse: [
			async (request, _options, response) => {
				if (response.status !== 401) return response

				// Éviter les boucles infinies sur la route de refresh elle-même
				if (request.url.includes("/api/token/refresh")) {
					useAuthStore.getState().logout()
					return response
				}

				if (isRefreshing) return response
				isRefreshing = true

				try {
					await ky.post(`${process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000"}/api/token/refresh`, {
						credentials: "include",
					})

					// Relancer la requête originale
					isRefreshing = false
					return ky(request)
				} catch {
					isRefreshing = false
					useAuthStore.getState().logout()
					return response
				}
			},
		],
	},
})
