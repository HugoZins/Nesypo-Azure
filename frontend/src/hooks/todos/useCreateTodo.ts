import { useMutation, useQueryClient } from "@tanstack/react-query";
import { todoApi } from "@/lib/todo.api";

export function useCreateTodo(todoListId: number) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (title: string) => todoApi.create(todoListId, title),
        onSuccess: () => {
            queryClient.invalidateQueries(["todos", todoListId]);
            queryClient.invalidateQueries(["todoLists"]); // si tu veux mettre à jour la progression
        },
    });
}
