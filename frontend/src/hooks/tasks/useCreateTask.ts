import { useMutation, useQueryClient } from "@tanstack/react-query"
import { toast } from "sonner"
import { taskApi } from "@/lib/taskApi"

export function useCreateTask(todoListId?: number) {
	const queryClient = useQueryClient()

	return useMutation({
		mutationFn: taskApi.create,
		onSuccess: async (_, variables) => {
			const id = variables.todoListId ?? todoListId
			await queryClient.invalidateQueries({ queryKey: ["tasks", id] })
			await queryClient.invalidateQueries({ queryKey: ["todoLists"] })
			toast.success("Tâche créée")
		},
		onError: () => {
			toast.error("Impossible de créer la tâche")
		},
	})
}
