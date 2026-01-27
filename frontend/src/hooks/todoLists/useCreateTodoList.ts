import {useMutation, useQueryClient} from "@tanstack/react-query";
import {todoListApi} from "@/lib/todoListApi";

export function useCreateTodoList() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (title: string) => todoListApi.create(title),
        onSuccess: () => {
            queryClient.invalidateQueries(["todoLists"]);
        },
    });
}
