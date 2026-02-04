import {useMutation, useQueryClient} from "@tanstack/react-query"
import {taskApi} from "@/lib/taskApi"
import type {Task} from "@/types/todo"

type CreateTaskPayload = {
    title: string
    todoListId: number
    priority?: string
}

export function useCreateTask(todoListId: number) {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: taskApi.create,

        onSuccess: () => {
            queryClient.invalidateQueries({
                queryKey: ["tasks", todoListId],
            })
        },
    })
}

