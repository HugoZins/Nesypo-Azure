import { useQuery } from "@tanstack/react-query";
import { todoApi } from "@/lib/todo.api";
import { Todo } from "@/types/todo";

export function useTodos(todoListId: number) {
    return useQuery<Todo[]>({
        queryKey: ["todos", todoListId],
        queryFn: () => todoApi.getAll(todoListId),
        staleTime: 1000 * 60 * 2,
    });
}
