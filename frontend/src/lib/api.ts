import ky from "ky"

export const api = ky.create({
	prefixUrl: "http://localhost:8000",
	credentials: "include",
	headers: {
		"Content-Type": "application/json",
		Accept: "application/json",
	},
	hooks: {
		afterResponse: [
			async (_request, _options, response) => {
				if (response.status === 401) {
					// JWT invalide / expiré / absent
					if (typeof window !== "undefined") {
						window.location.href = "/login"
					}
				}
			},
		],
	},
})
