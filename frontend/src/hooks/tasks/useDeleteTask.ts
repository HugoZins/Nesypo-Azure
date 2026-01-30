import { useMutation, useQueryClient } from "@tanstack/react-query"
import { taskApi } from "@/lib/taskApi"
import type { Task } from "@/types/todo"

export function useDeleteTask(todoListId: number) {
	const queryClient = useQueryClient()

	return useMutation<void, unknown, number>({
		mutationFn: (taskId) => taskApi.delete(taskId),

		onMutate: async (taskId) => {
			await queryClient.cancelQueries({ queryKey: ["tasks", todoListId] })

			const previousTasks = queryClient.getQueryData<Task[]>(["tasks", todoListId])

			queryClient.setQueryData<Task[]>(["tasks", todoListId], (old) => old?.filter((task) => task.id !== taskId))

			return { previousTasks }
		},

		onError: (_err, _taskId, context) => {
			if (context?.previousTasks) {
				queryClient.setQueryData(["tasks", todoListId], context.previousTasks)
			}
		},

		onSettled: () => {
			queryClient.invalidateQueries({ queryKey: ["tasks", todoListId] })
			queryClient.invalidateQueries({ queryKey: ["todoLists"] })
		},
	})
}
