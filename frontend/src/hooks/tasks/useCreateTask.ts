import { useMutation, useQueryClient } from "@tanstack/react-query"
import { api } from "@/lib/api"
import type { Task } from "@/types/todo"

type CreateTaskPayload = {
	title: string
	todoListId: number
	priority?: string
}

export function useCreateTask(_todoListId: number) {
	const queryClient = useQueryClient()

	return useMutation<Task, unknown, CreateTaskPayload>({
		mutationFn: (payload) => api.post("api/tasks-custom", { json: payload }).json(),
		onSuccess: () => {
			queryClient.invalidateQueries(["todoLists"]) // invalide la liste complète
		},
	})
}
