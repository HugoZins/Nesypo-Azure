import { useQuery } from "@tanstack/react-query";
import { taskApi } from "@/lib/taskApi";
import { Task } from "@/types/todo";

export function useTodoListTasks(todoListId?: number) {
    return useQuery<Task[]>({
        queryKey: ["tasks", todoListId],
        queryFn: () => taskApi.getByTodoList(todoListId!),
        enabled: !!todoListId,
    });
}
