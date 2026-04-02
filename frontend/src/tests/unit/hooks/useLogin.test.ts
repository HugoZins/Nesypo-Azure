import { renderHook, act, waitFor } from "@testing-library/react"
import { describe, expect, it } from "vitest"
import { useLogin } from "@/hooks/auth/useLogin"
import { createTestQueryClient } from "../../utils"
import { QueryClient, QueryClientProvider } from "@tanstack/react-query"
import { createElement } from "react"

function wrapper({ children }: { children: React.ReactNode }) {
    const queryClient = createTestQueryClient()
    return createElement(QueryClientProvider, { client: queryClient }, children)
}

describe("useLogin", () => {
    it("est initialement en état idle", () => {
        const { result } = renderHook(() => useLogin(), { wrapper })

        expect(result.current.isPending).toBe(false)
        expect(result.current.isSuccess).toBe(false)
        expect(result.current.isError).toBe(false)
    })

    it("passe en succès après un login valide", async () => {
        const { result } = renderHook(() => useLogin(), { wrapper })

        act(() => {
            result.current.mutate({ email: "test@mail.com", password: "Password123" })
        })

        await waitFor(() => {
            expect(result.current.isSuccess).toBe(true)
        })
    })

    it("passe en erreur avec des identifiants invalides", async () => {
        const { http, HttpResponse } = await import("msw")
        const { server } = await import("../../mocks/server")

        server.use(
            http.post("http://localhost:8000/api/login", () => {
                return HttpResponse.json(
                    { message: "Invalid credentials" },
                    { status: 401 }
                )
            })
        )

        const { result } = renderHook(() => useLogin(), { wrapper })

        act(() => {
            result.current.mutate({ email: "wrong@mail.com", password: "wrong" })
        })

        await waitFor(() => {
            expect(result.current.isError).toBe(true)
        })
    })
})