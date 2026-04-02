export type TaskPriority = "Basse" | "Moyenne" | "Haute"

export type TodoList = {
    id: number
    title: string
    ownerEmail?: string | null
    progress?: number
    completedTasks?: number
    totalTasks?: number
}

export type Task = {
    id: number
    title: string
    done: boolean
    priority?: TaskPriority
    todoListId: number
}

export type Me = {
    id: number
    email: string
    roles: string[]
}

export type PaginatedResponse<T> = {
    data: T[]
    total: number
    page: number
    limit: number
    pages: number
}