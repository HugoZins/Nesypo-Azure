import { useMutation, useQueryClient } from "@tanstack/react-query"
import { toast } from "sonner"
import { todoListApi } from "@/lib/todoListApi"

export function useDeleteTodoList(onSuccess?: () => void) {
	const queryClient = useQueryClient()

	return useMutation({
		mutationFn: (id: number) => todoListApi.delete(id),
		onSuccess: () => {
			queryClient.invalidateQueries({ queryKey: ["todoLists"] })
			toast.success("Liste supprimée")
			onSuccess?.()
		},
		onError: () => {
			toast.error("Impossible de supprimer la liste")
		},
	})
}