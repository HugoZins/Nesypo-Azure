import { useQuery } from "@tanstack/react-query"
import { todoListApi } from "@/lib/todoListApi"

export function useTodoLists(page = 1, limit = 10) {
	return useQuery({
		queryKey: ["todo-lists", page, limit],
		queryFn: () => todoListApi.getAll(page, limit),
		placeholderData: (prev) => prev, // garde les données précédentes pendant le chargement
	})
}