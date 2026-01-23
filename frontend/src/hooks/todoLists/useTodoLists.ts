import {useQuery} from "@tanstack/react-query";
import {todoListApi} from "@/lib/todoListApi";
import {TodoList} from "@/types/todo";

export function useTodoLists() {
    return useQuery<TodoList[]>({
        queryKey: ["todoLists"],
        queryFn: () => todoListApi.getAll(),
        staleTime: 1000 * 60 * 2,
    });
}
