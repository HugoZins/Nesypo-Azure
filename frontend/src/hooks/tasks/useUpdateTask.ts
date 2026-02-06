import { useMutation, useQueryClient } from "@tanstack/react-query"
import { taskApi } from "@/lib/taskApi"
import type { Task } from "@/types/todo"

type UpdateTaskPayload = {
	id: number
	data: Partial<{
		title: string
		priority: "Basse" | "Moyenne" | "Haute"
		done: boolean
	}>
}

type MutationContext = {
	previousTasks?: Task[]
}

export function useUpdateTask(todoListId: number) {
	const queryClient = useQueryClient()

	return useMutation<Task, unknown, UpdateTaskPayload, MutationContext>({
		mutationFn: ({ id, data }) => taskApi.update(id, data),

		onMutate: async ({ id, data }) => {
			await queryClient.cancelQueries({
				queryKey: ["tasks", todoListId],
			})

			const previousTasks = queryClient.getQueryData<Task[]>(["tasks", todoListId])

			queryClient.setQueryData<Task[]>(["tasks", todoListId], (old) =>
				old?.map((task) => (task.id === id ? { ...task, ...data } : task)),
			)

			return { previousTasks }
		},

		onError: (_err, _vars, context) => {
			if (context?.previousTasks) {
				queryClient.setQueryData(["tasks", todoListId], context.previousTasks)
			}
		},

		onSettled: () => {
			// important : invalidate tasks + todoLists
			queryClient.invalidateQueries({ queryKey: ["tasks", todoListId] })
			queryClient.invalidateQueries({ queryKey: ["todoLists"] })
		},
	})
}
