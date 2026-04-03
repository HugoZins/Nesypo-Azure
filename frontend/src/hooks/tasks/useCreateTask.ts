import { useMutation, useQueryClient } from "@tanstack/react-query"
import { toast } from "sonner"
import { taskApi } from "@/lib/taskApi"

type CreateTaskPayload = {
	title: string
	todoListId: number
	priority?: "Basse" | "Moyenne" | "Haute"
}

export function useCreateTask(todoListId?: number) {
	const queryClient = useQueryClient()

	return useMutation({
		mutationFn: taskApi.create,
		onSuccess: (_, variables) => {
			const id = variables.todoListId ?? todoListId
			queryClient.invalidateQueries({ queryKey: ["tasks", id] })
			queryClient.invalidateQueries({ queryKey: ["todoLists"] })
			toast.success("Tâche créée")
		},
		onError: () => {
			toast.error("Impossible de créer la tâche")
		},
	})
}
