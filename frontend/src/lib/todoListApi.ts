import { api } from "./api";
import { TodoList } from "@/types/todo";

export const todoListApi = {
    getAll: () => api.get("api/todo-lists", { headers: { accept: "application/json" } }).json<TodoList[]>(),
    create: (title: string) => api.post("api/todo-lists", { json: { title } }).json<TodoList>(),
};
