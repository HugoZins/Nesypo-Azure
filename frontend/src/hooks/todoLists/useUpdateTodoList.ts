import { useMutation, useQueryClient } from "@tanstack/react-query"
import { todoListApi } from "@/lib/todoListApi"
import type { TodoList } from "@/types/todo"

type UpdateTodoListPayload = {
	id: number
	data: Partial<{ title: string }>
}

export function useUpdateTodoList() {
	const queryClient = useQueryClient()

	return useMutation<TodoList, unknown, UpdateTodoListPayload>({
		mutationFn: ({ id, data }) => todoListApi.update(id, data),

		onSuccess: () => {
			queryClient.invalidateQueries(["todoLists"])
		},
	})
}
