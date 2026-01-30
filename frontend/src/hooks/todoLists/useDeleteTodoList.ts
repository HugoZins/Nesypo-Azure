import { useMutation, useQueryClient } from "@tanstack/react-query"
import { todoListApi } from "@/lib/todoListApi"

export function useDeleteTodoList() {
	const queryClient = useQueryClient()

	return useMutation({
		mutationFn: (id: number) => todoListApi.delete(id),

		onSuccess: () => {
			queryClient.invalidateQueries(["todoLists"])
		},
	})
}
