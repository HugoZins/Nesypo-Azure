import { useQuery } from "@tanstack/react-query"
import { taskApi } from "@/lib/taskApi"
import type { Task } from "@/types/todo"

export function useTasks(todoListId?: number) {
	return useQuery<Task[]>({
		queryKey: ["tasks", todoListId],
		queryFn: () => {
			if (!todoListId) {
				throw new Error("todoListId is required")
			}
			return taskApi.getByTodoList(todoListId)
		},
		enabled: !!todoListId,
		staleTime: 1000 * 60 * 2,
	})
}

