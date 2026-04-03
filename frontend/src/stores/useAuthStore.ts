import { create } from "zustand"

interface AuthUser {
	id: number
	email: string
	roles: string[]
}

interface AuthState {
	isAuthenticated: boolean
	user: AuthUser | null
	setAuth: (user: AuthUser) => void
	logout: () => void
}

export const useAuthStore = create<AuthState>((set) => ({
	isAuthenticated: false,
	user: null,
	setAuth: (user) => set({ isAuthenticated: true, user }),
	logout: () => {
		set({ isAuthenticated: false, user: null })
		if (typeof window !== "undefined") {
			window.location.href = "/login"
		}
	},
}))
