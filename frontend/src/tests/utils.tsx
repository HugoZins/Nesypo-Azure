import { QueryClient, QueryClientProvider } from "@tanstack/react-query"
import { type RenderOptions, render } from "@testing-library/react"
import type { ReactNode } from "react"

export function createTestQueryClient() {
	return new QueryClient({
		defaultOptions: {
			queries: {
				retry: false,
				gcTime: 0,
				staleTime: 0,
			},
			mutations: {
				retry: false,
			},
		},
	})
}

function TestWrapper({ children }: { children: ReactNode }) {
	const queryClient = createTestQueryClient()
	return <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
}

export function renderWithProviders(ui: React.ReactElement, options?: RenderOptions) {
	return render(ui, { wrapper: TestWrapper, ...options })
}
