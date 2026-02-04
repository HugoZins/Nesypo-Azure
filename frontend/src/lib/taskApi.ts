import type {Task} from "@/types/todo"
import {api} from "./api"

type CreateTaskPayload = {
    title: string
    todoListId: number
    priority?: "low" | "medium" | "high"
}

export const taskApi = {
    getByTodoList(todoListId: number) {
        return api.get(`api/todo-lists/${todoListId}/tasks`).json<Task[]>()
    },

    create(payload: CreateTaskPayload) {
        return api.post("api/tasks", {json: payload}).json<Task>()
    },

    update(id: number, payload: Partial<Task>) {
        return api.patch(`api/tasks/${id}`, {json: payload}).json<Task>()
    },

    delete(id: number) {
        return api.delete(`api/tasks/${id}`).json()
    },
}
