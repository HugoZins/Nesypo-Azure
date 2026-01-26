import {api} from "./api";
import {Task} from "@/types/todo";

export const taskApi = {
    getByTodoList(todoListId: number) {
        return api
            .get(`api/todo-lists/${todoListId}/tasks`)
            .json<Task[]>();
    },

    create(todoListId: number, title: string) {
        return api
            .post("api/tasks", {json: {title, todoListId},})
            .json<Task>();
    },

    update(id: number, payload: Partial<Task>) {
        return api
            .put(`api/tasks/${id}`, {json: payload,})
            .json<Task>();
    },

    delete(id: number) {
        return api.delete(`api/tasks/${id}`)
            .json();
    },
};
