import {api} from "./api";
import {TodoList} from "@/types/todo";

export const todoListApi = {
    getAll: () => api.get("api/todo-lists", {headers: {accept: "application/json"}}).json<TodoList[]>(),

    create: (title: string) => api.post("api/todo-lists", {json: {title}}).json<TodoList>(),

    getById(id: number) {
        return api.get(`api/todo-lists/${id}`).json<TodoList>();
    },

    update(id: number, payload: Partial<TodoList>) {
        return api.put(`api/todo-lists/${id}`, {json: payload}).json<TodoList>();
    },

    delete(id: number) {
        return api.delete(`api/todo-lists/${id}`).json();
    },

};
