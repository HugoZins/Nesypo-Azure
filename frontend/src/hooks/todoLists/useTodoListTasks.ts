import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";

type Task = {
    id: number;
    title: string;
    done: boolean;
    priority?: string;
    todoList: { id: number };
};

export function useTodoListTasks(todoListId?: number | string) {
    return useQuery<Task[]>({
        queryKey: ["todoListTasks", todoListId],
        queryFn: () =>
            api
                .get(`api/tasks?todoListId=${todoListId}`)
                .json<Task[]>(),
        enabled: !!todoListId,
        staleTime: 1000 * 60 * 2,
    });
}
