import { useMutation, useQueryClient } from "@tanstack/react-query"
import { toast } from "sonner"
import { todoListApi } from "@/lib/todoListApi"
import type { TodoList } from "@/types/todo"

export function useCreateTodoList() {
	const queryClient = useQueryClient()

	return useMutation<TodoList, Error, string>({
		mutationFn: (title: string) => todoListApi.create(title),
		onSuccess: () => {
			queryClient.invalidateQueries({ queryKey: ["todoLists"] })
			toast.success("Liste créée")
		},
		onError: () => {
			toast.error("Impossible de créer la liste")
		},
	})
}