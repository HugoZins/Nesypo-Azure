import { useQuery } from "@tanstack/react-query"
import { api } from "@/lib/api"
import type { Me } from "@/types/todo"

export function useMe() {
	return useQuery<Me>({
		queryKey: ["me"],
		queryFn: () => api.get("api/me").json<Me>(),
		staleTime: 1000 * 60 * 5,
		retry: false,
	})
}
