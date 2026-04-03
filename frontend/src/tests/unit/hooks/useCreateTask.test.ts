import { QueryClientProvider } from "@tanstack/react-query"
import { act, renderHook, waitFor } from "@testing-library/react"
import { createElement } from "react"
import { describe, expect, it } from "vitest"
import { useCreateTask } from "@/hooks/tasks/useCreateTask"
import { createTestQueryClient } from "../../utils"

function wrapper({ children }: { children: React.ReactNode }) {
	const queryClient = createTestQueryClient()
	return createElement(QueryClientProvider, { client: queryClient }, children)
}

describe("useCreateTask", () => {
	it("est initialement en état idle", () => {
		const { result } = renderHook(() => useCreateTask(1), { wrapper })

		expect(result.current.isPending).toBe(false)
		expect(result.current.isSuccess).toBe(false)
	})

	it("crée une tâche avec succès", async () => {
		const { result } = renderHook(() => useCreateTask(1), { wrapper })

		act(() => {
			result.current.mutate({
				title: "Nouvelle tâche",
				priority: "Haute",
				todoListId: 1,
			})
		})

		await waitFor(() => {
			expect(result.current.isSuccess).toBe(true)
		})
	})

	it("passe en erreur si l'API échoue", async () => {
		const { http, HttpResponse } = await import("msw")
		const { server } = await import("../../mocks/server")

		server.use(
			http.post("http://localhost:8000/api/tasks", () => {
				return HttpResponse.json({ message: "Données invalides" }, { status: 400 })
			}),
		)

		const { result } = renderHook(() => useCreateTask(1), { wrapper })

		act(() => {
			result.current.mutate({
				title: "",
				todoListId: 1,
			})
		})

		await waitFor(() => {
			expect(result.current.isError).toBe(true)
		})
	})
})
