import { api } from "@/lib/api"
import type { PaginatedResponse, TodoList } from "@/types/todo"

export const todoListApi = {
	getAll: (page = 1, limit = 10) =>
		api.get("api/todo-lists", { searchParams: { page, limit } }).json<PaginatedResponse<TodoList>>(),

	getOne: (id: number) => api.get(`api/todo-lists/${id}`).json<TodoList>(),

	create: (title: string) => api.post("api/todo-lists", { json: { title } }).json<TodoList>(),

	update: (id: number, title: Partial<{ title: string }>) =>
		api.put(`api/todo-lists/${id}`, { json: { title } }).json<TodoList>(),

	delete: (id: number) => api.delete(`api/todo-lists/${id}`).json<{ status: string }>(),
}
