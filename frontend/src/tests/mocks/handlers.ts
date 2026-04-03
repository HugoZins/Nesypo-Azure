import { HttpResponse, http } from "msw"

const BASE_URL = "http://localhost:8000"

export const handlers = [
	http.post(`${BASE_URL}/api/login`, () => {
		return HttpResponse.json({ status: "success" })
	}),

	http.post(`${BASE_URL}/api/register`, () => {
		return HttpResponse.json({ id: 1, email: "test@mail.com" })
	}),

	http.post(`${BASE_URL}/api/logout`, () => {
		return HttpResponse.json({ status: "logged_out" })
	}),

	http.get(`${BASE_URL}/api/me`, () => {
		return HttpResponse.json({
			id: 1,
			email: "test@mail.com",
			roles: ["ROLE_USER"],
		})
	}),

	http.get(`${BASE_URL}/api/todo-lists`, () => {
		return HttpResponse.json({
			data: [
				{ id: 1, title: "Liste 1", progress: 50, completedTasks: 1, totalTasks: 2 },
				{ id: 2, title: "Liste 2", progress: 0, completedTasks: 0, totalTasks: 3 },
			],
			total: 2,
			page: 1,
			limit: 10,
			pages: 1,
		})
	}),

	http.post(`${BASE_URL}/api/todo-lists`, () => {
		return HttpResponse.json(
			{ id: 3, title: "Nouvelle liste", progress: 0, completedTasks: 0, totalTasks: 0 },
			{ status: 201 },
		)
	}),

	http.get(`${BASE_URL}/api/todo-lists/:id`, ({ params }) => {
		return HttpResponse.json({
			id: Number(params.id),
			title: "Liste test",
			progress: 50,
			completedTasks: 1,
			totalTasks: 2,
		})
	}),

	http.get(`${BASE_URL}/api/todo-lists/:id/tasks`, () => {
		return HttpResponse.json([
			{ id: 1, title: "Tâche 1", done: false, priority: "Haute" },
			{ id: 2, title: "Tâche 2", done: true, priority: "Basse" },
		])
	}),

	http.post(`${BASE_URL}/api/tasks`, () => {
		return HttpResponse.json({ id: 10, title: "Nouvelle tâche", done: false, priority: "Moyenne" })
	}),

	http.patch(`${BASE_URL}/api/tasks/:id`, ({ params }) => {
		return HttpResponse.json({ id: Number(params.id), title: "Tâche modifiée", done: true, priority: "Haute" })
	}),

	http.delete(`${BASE_URL}/api/tasks/:id`, () => {
		return HttpResponse.json({ status: "success" })
	}),

	http.post(`${BASE_URL}/api/token/refresh`, () => {
		return HttpResponse.json({ message: "Invalid refresh token" }, { status: 401 })
	}),
]
