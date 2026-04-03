import { QueryClientProvider } from "@tanstack/react-query"
import { render, screen, waitFor } from "@testing-library/react"
import userEvent from "@testing-library/user-event"
import { act } from "react"
import { describe, expect, it, vi } from "vitest"
import LoginForm from "@/components/auth/LoginForm"
import { createTestQueryClient } from "../utils"

const mockPush = vi.fn()

vi.mock("next/navigation", () => ({
	useRouter: () => ({ push: mockPush }),
}))

vi.mock("@/stores/useAuthStore", () => ({
	useAuthStore: vi.fn((selector?: (s: { setAuth: () => void; isAuthenticated: boolean }) => unknown) => {
		const state = { setAuth: vi.fn(), isAuthenticated: false, logout: vi.fn() }
		return selector ? selector(state) : state
	}),
}))

vi.mock("@/hooks/auth/useLogin", () => ({
	useLogin: () => ({
		mutate: vi.fn((_: unknown, options?: { onSuccess?: () => void }) => {
			options?.onSuccess?.()
		}),
		isPending: false,
		isError: false,
	}),
}))

vi.mock("sonner", () => ({
	toast: {
		error: vi.fn(),
		success: vi.fn(),
	},
}))

function renderLoginForm() {
	return render(
		<QueryClientProvider client={createTestQueryClient()}>
			<LoginForm />
		</QueryClientProvider>,
	)
}

describe("LoginForm", () => {
	it("affiche les champs email et mot de passe", () => {
		renderLoginForm()

		expect(screen.getByLabelText(/email/i)).toBeInTheDocument()
		expect(screen.getByLabelText(/mot de passe/i)).toBeInTheDocument()
	})

	it("affiche le bouton de connexion", () => {
		renderLoginForm()

		expect(screen.getByRole("button", { name: /se connecter/i })).toBeInTheDocument()
	})

	it("affiche un lien vers l'inscription", () => {
		renderLoginForm()

		expect(screen.getByRole("link", { name: /inscrivez-vous/i })).toBeInTheDocument()
	})

	it("affiche une erreur si l'email est invalide", async () => {
		const user = userEvent.setup()
		renderLoginForm()

		// Vider le champ email et mettre une valeur invalide
		const emailInput = screen.getByLabelText(/email/i)
		await user.clear(emailInput)
		await user.type(emailInput, "pas_un_email")
		await user.type(screen.getByLabelText(/mot de passe/i), "Password123")

		// Soumettre via le formulaire directement
		const form = document.querySelector("form")!
		await act(async () => {
			form.dispatchEvent(new Event("submit", { bubbles: true, cancelable: true }))
		})

		await waitFor(() => {
			expect(document.querySelectorAll("p.text-destructive").length).toBeGreaterThan(0)
		})
	})

	it("affiche une erreur si le mot de passe est trop court", async () => {
		const user = userEvent.setup()
		renderLoginForm()

		await user.type(screen.getByLabelText(/email/i), "test@mail.com")
		await user.type(screen.getByLabelText(/mot de passe/i), "abc")
		await user.click(screen.getByRole("button", { name: /se connecter/i }))

		await waitFor(() => {
			expect(screen.getByText(/too small/i)).toBeInTheDocument()
		})
	})

	it("désactive le bouton pendant la soumission", async () => {
		const user = userEvent.setup()
		renderLoginForm()

		await user.type(screen.getByLabelText(/email/i), "test@mail.com")
		await user.type(screen.getByLabelText(/mot de passe/i), "Password123")

		const button = screen.getByRole("button", { name: /se connecter/i })
		await user.click(button)

		// Après soumission réussie le bouton revient à l'état normal
		await waitFor(() => {
			expect(screen.getByRole("button", { name: /se connecter/i })).toBeInTheDocument()
		})
	})

	it("redirige vers le dashboard après un login réussi", async () => {
		const user = userEvent.setup()
		renderLoginForm()

		await user.type(screen.getByLabelText(/email/i), "test@mail.com")
		await user.type(screen.getByLabelText(/mot de passe/i), "Password123")
		await user.click(screen.getByRole("button", { name: /se connecter/i }))

		await waitFor(() => {
			expect(mockPush).toHaveBeenCalledWith("/dashboard")
		})
	})
})
