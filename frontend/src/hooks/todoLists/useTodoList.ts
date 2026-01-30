import { useQuery } from "@tanstack/react-query"
import { todoListApi } from "@/lib/todoListApi"
import type { TodoList } from "@/types/todo"

export function useTodoList(id?: number | string) {
	return useQuery<TodoList>({
		queryKey: ["todoList", id],
		queryFn: () => todoListApi.getById(Number(id)),
		enabled: !!id,
		staleTime: 1000 * 60 * 2,
	})
}
