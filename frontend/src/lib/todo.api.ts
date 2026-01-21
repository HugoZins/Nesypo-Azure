import { api } from "./api";
import { Todo } from "@/types/todo";

export const todoApi = {
    getAll: (todoListId: number) =>
        api
            .get(`api/todo-lists/${todoListId}/todos`, {
                headers: { accept: "application/json" },
            })
            .json<Todo[]>(),

    create: (todoListId: number, title: string) =>
        api
            .post(`api/todo-lists/${todoListId}/todos`, {
                json: { title },
            })
            .json<Todo>(),

    toggleDone: (todoId: number, done: boolean) =>
        api
            .patch(`api/todos/${todoId}`, {
                json: { done },
            })
            .json<Todo>(),

    delete: (todoId: number) =>
        api.delete(`api/todos/${todoId}`).json(),
};
